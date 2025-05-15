<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

require_once 'config/database.php';
require_once 'config/key.php';
require_once 'services/suitPayService.php';
require_once 'middleware/admin.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (preg_match('/^https:\/\/([a-z0-9-]+\.)?bingotop\.com\.br$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
}


$session_id = $_COOKIE['session'];
$uuid = validate_uuid($conn, $session_id);



if (!$uuid) {
    // Adiciona o registro disso no banco de dados.
    $action = "Tentativa falha de liberação de saque na plataforma de admin. Dados: UUID: " . $uuid;
    $location = "api/createWithdrawApproval.php";
    $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
    $insert_log_stmt = $conn->prepare($insert_log_sql);
    $insert_log_stmt->bind_param(
        "sss",
        $uuid,
        $action,
        $location
    );
    $insert_log_stmt->execute();
    $insert_log_stmt->close();

    die("Error: Unauthorized.");
}

$withdrawId = $_POST['withdrawId'];
$type = $_POST['type'];

if ($type == 'accept') {

    // Pegar as informações do saque.
    $sql = "SELECT * FROM withdraws WHERE withdraw_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $withdrawId);
    $result = $stmt->execute();
    $response_data = $stmt->get_result();
    $data = $response_data->fetch_assoc();

    if ($data['withdraw_pixtype'] == "TELEFONE") {
        $data['withdraw_pixkey'] = preg_replace('/[^0-9]/', '', $data['withdraw_pixkey']);
        if (substr($data['withdraw_pixkey'], 0, 3) !== "+55") {
            if (substr($data['withdraw_pixkey'], 0, 2) !== "55") {
                $data['withdraw_pixkey'] = "+55" . $data['withdraw_pixkey'];
            } else {
                $data['withdraw_pixkey'] = "+" . $data['withdraw_pixkey'];
            }
        }
    } else if ($data['withdraw_pixtype'] == "CPF") {
        $data['withdraw_pixkey'] = preg_replace('/[^0-9]/', '', $data['withdraw_pixkey']);
    }

    $keyType = $data['withdraw_pixtype'] == "CPF" ? "document" : "phoneNumber";

    $result = generateSuitPayPixTransfer(
        $apiUrl,
        $client_id_spay,
        $client_secret_spay,
        $data['withdraw_pixkey'],
        $keyType,
        $data['quantia'],
        'https://bingotop.com.br/api/webhook/withDraw.php',
        $data['withdraw_cpf'],
        $data['withdraw_id']
    );

    $suitPay_id = $result['idTransaction'];
    $httpCode = $result['httpCode'];
    $respCode = $result['response'];

    if (($httpCode === 200 || $httpCode === 201) && $respCode === 'OK') {

        $update_user_prizes_sql = "UPDATE users_prizes SET saque_status = 0 WHERE saque_status = 1  AND id_user = ?";
        $update_user_prizes_stmt = $conn->prepare($update_user_prizes_sql);
        $update_user_prizes_stmt->bind_param(
            "i",
            $withdraw_data['user_id']
        );
        $update_user_prizes_stmt->execute();

        $sql = "UPDATE withdraws SET status = 1, suitpay_id = ? WHERE withdraw_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $suitPay_id, $withdrawId);
        $stmt->execute();

        echo json_encode(['message' => 'Inserido com sucesso, Aguardando confirmação da SuitPay.', 'code' => 200]);

        // Insere a informação de validação do admin no banco de dados
        $action = "Liberação de saque na plataforma de admin. ID do saque: " . $withdrawId;
        $location = "api/createWithdrawApproval.php";
        $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
        $insert_log_stmt = $co->prepare($insert_log_sql);
        $insert_log_stmt->bind_param(
            "sss",
            $uuid,
            $action,
            $location
        );
        $insert_log_stmt->execute();
        $insert_log_stmt->close();
    } else {
        // Falha no PIX
        $sql = "UPDATE withdraws SET status = 2 WHERE withdraw_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $withdrawId);
        $stmt->execute();

        $userId = $data['user_id'];
        $update_prizes_sql  = "UPDATE users_prizes SET saque_status = 0 WHERE id_user = ? AND saque_status = 1";
        $update_prizes_stmt = $conn->prepare($update_prizes_sql);
        $update_prizes_stmt->bind_param('i', $userId);
        $update_prizes_stmt->execute();

        // Mapeia resposta de erro
        $messages = [
            'PIX_KEY_NOT_FOUND'   => 'Chave PIX não encontrada.',
            'DOCUMENT_VALIDATE'   => 'Chave não pertence ao documento.',
            'DUPLICATE_EXTERNAL_ID' => 'Transação duplicada.',
        ];
        $msg = $messages[$respCode] ?? 'Erro desconhecido. Contate o suporte.';

        echo json_encode(['erro' => "Não foi possível realizar a transferência! Erro: {$msg}", 'code' => 400]);
    }
} else {

    $withdraw_sql = "SELECT * FROM withdraws WHERE withdraw_id = ?";
    $withdraw_stmt = $conn->prepare($withdraw_sql);
    $withdraw_stmt->bind_param('i', $withdrawId);
    $withdraw_result = $withdraw_stmt->execute();
    $withdraw_response_data = $withdraw_stmt->get_result();
    $withdraw_data = $withdraw_response_data->fetch_assoc();

    // Atualiza o saque com o status 2, o status negado.
    $sql = "UPDATE withdraws SET status = 2 WHERE withdraw_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "i",
        $withdrawId
    );
    $stmt->execute();

    // Insere a informação de validação do admin no banco de dados
    $action = "Bloqueio de saque na plataforma de admin. ID do saque: " . $withdrawId;
    $location = "api/createWithdrawApproval.php";
    $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
    $insert_log_stmt = $conn->prepare($insert_log_sql);
    $insert_log_stmt->bind_param(
        "sss",
        $uuid,
        $action,
        $location
    );
    $insert_log_stmt->execute();
    $insert_log_stmt->close();

    echo ("Sucesso!");
}
