<?php
include 'connection.php';
include_once 'functions.php';

// Valida o uuid para isso
if (empty($_COOKIE['session'])) {
    error_log("Sessão não encontrada: cookie 'session' ausente ou vazio.");
    header('HTTP/1.1 401 Unauthorized');
    exit("Error: Session cookie not found.");
}

$uuid = validate_uuid($mysqli, $_COOKIE['session']);

$uid = $_POST['uid'] ?? null;
$nome = $_POST['nome'] ?? null;
$cpf = $_POST['cpf'] ?? null;
$email = $_POST['email'] ?? null;
$telefone = $_POST['telefone'] ?? null;
$codigo = $_POST['codigo'] ?? null;
$saldo = $_POST['saldo'] ?? null;
$saldo_bonus = $_POST['saldoBonus'] ?? null;

$sql = "UPDATE users SET nome = ?, email = ?, telefone = ?, codigo = ?, saldo = ?, saldo_bonus = ? WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "ssssddi",
    $nome,
    $email,
    $telefone,
    $codigo,
    $saldo,
    $saldo_bonus,
    $uid
);
$stmt->execute();
$response_data = $stmt->get_result();

// Adiciona o registro disso no banco de dados.
$action = "Alteração dos dados de usuários na plataforma de admin. Dados: UID: " . $uid;
$location = "php/update-user.php";
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
