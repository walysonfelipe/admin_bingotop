<?php

date_default_timezone_set('America/Sao_Paulo');

include 'connection.php';
include 'server_settings.php';
include 'functions.php';

$session_id = $_COOKIE['session'];
$uuid = validate_uuid($mysqli, $session_id);

if (!$uuid) {
    // Adiciona o registro disso no banco de dados.
    $action = "Tentativa falha de liberação de saque na plataforma de admin. Dados: UUID: " . $uuid;
    $location = "php/handle-withdraw.php";
    $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
    $insert_log_stmt = $mysqli->prepare($insert_log_sql);
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
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $withdrawId);
    $result = $stmt->execute();
    $response_data = $stmt->get_result();
    $data = $response_data->fetch_assoc();

    // Realiza a autenticação, pegando o Bearer Token na EzzeBank
    $basic_auth = base64_encode($client_id . ":" . $client_secret);

    $auth_headers = array(
        "Authorization: Basic " . $basic_auth
    );

    $auth_bearer_post = array('grant_type' => "client_credentials");

    // Get AuthToken
    $ch = curl_init('https://api.ezzebank.com/v2/oauth/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $auth_bearer_post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $auth_headers);

    $curl_response = curl_exec($ch);
    $ezzebank_bearer = json_decode($curl_response);

    $bearer = $ezzebank_bearer->access_token;

    curl_close($ch);

    $deposit_headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        "Authorization: Bearer " . $bearer
    ];

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

    // Remove www. for production purposes
    $deposit_body = [
        "amount" => $data['quantia'],
        "description" => "Saque da plataforma ShowPIX",
        "external_id" => $withdrawId,
        "creditParty" => [
            "key" => $data['withdraw_pixkey'],
            "keyType" => $data['withdraw_pixtype'],
            "name" => $data['withdraw_nome'],
            "taxId" => $data['withdraw_cpf']
        ]
    ];

    echo (json_encode($deposit_body));
    $ch = curl_init('https://api.ezzebank.com/v2/pix/payment');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($deposit_body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $deposit_headers);

    $pagfast_response = curl_exec($ch);
    $response = json_decode($pagfast_response, true);
    print_r($response);

    curl_close($ch);

    // Atualiza o saque com o status 1, o status aprovado.
    $sql = "UPDATE withdraws SET status = 1 WHERE withdraw_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        "i",
        $withdrawId
    );
    $stmt->execute();

    echo ('{"message": "Sucesso na transferência!", "transactionId": ' . $withdrawId . '}');

    // Variáveis para o extrato do usuário. Tipo de transação é 3 (saque). Tipo de valor da transação é 1 (valor real).
    $userId = $data['user_id'];
    $amount = $data['quantia'];
    $now_datetime = date('Y-m-d H:i:s');
    $transaction_type = 3;
    $statement_title = "Saque de prêmios";
    $statement_description = "ID do saque: " . $withdrawId . " - Saque de prêmios via PIX no valor de " . $data['quantia'] . " para chave: " . $data['withdraw_pixkey'];
    $statement_value_type = 1;

    // Insere o registro no extrato do cliente
    $insert_statement_sql = "INSERT INTO statement VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, ?)";
    $insert_statement_stmt = $mysqli->prepare($insert_statement_sql);
    $insert_statement_stmt->bind_param(
        "iidssis",
        $userId,
        $transaction_type,
        $amount,
        $statement_title,
        $statement_description,
        $statement_value_type,
        $now_datetime
    );
    $insert_statement_stmt->execute();

    // Insere a informação de validação do admin no banco de dados
    $action = "Liberação de saque na plataforma de admin. ID do saque: " . $withdrawId;
    $location = "php/handle-withdraw.php";
    $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
    $insert_log_stmt = $mysqli->prepare($insert_log_sql);
    $insert_log_stmt->bind_param(
        "sss",
        $uuid,
        $action,
        $location
    );
    $insert_log_stmt->execute();
    $insert_log_stmt->close();
} else {
    // Atualiza o saque com o status 2, o status negado.
    $sql = "UPDATE withdraws SET status = 2 WHERE withdraw_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        "i",
        $withdrawId
    );
    $stmt->execute();

    // Pegar as informações do saque.
    $withdraw_sql = "SELECT * FROM withdraws WHERE withdraw_id = ?";
    $withdraw_stmt = $mysqli->prepare($withdraw_sql);
    $withdraw_stmt->bind_param('i', $withdrawId);
    $withdraw_result = $withdraw_stmt->execute();
    $withdraw_response_data = $withdraw_stmt->get_result();
    $withdraw_data = $withdraw_response_data->fetch_assoc();

    // Insere a informação de validação do admin no banco de dados
    $action = "Bloqueio de saque na plataforma de admin. ID do saque: " . $withdrawId;
    $location = "php/handle-withdraw.php";
    $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
    $insert_log_stmt = $mysqli->prepare($insert_log_sql);
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
