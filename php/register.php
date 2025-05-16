<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

require_once 'connection.php';

$email = "contato.rusian@gmail.com";
$senha = "B1ng0#05";

if (!$email || !$senha) {
    echo json_encode([
        "success" => false,
        "message" => "Email e senha são obrigatórios.",
    ]);
    exit;
}

// Verifica se o usuário já existe
$check_sql = "SELECT 1 FROM admin_users WHERE admin_email = ?";
$stmt = $mysqli->prepare($check_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Este email já está cadastrado."
    ]);
    exit;
}

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_BCRYPT);
$uuid = bin2hex(random_bytes(16));
$privileges = 2; // ou outro valor padrão

// Insere o novo usuário
$insert_sql = "INSERT INTO admin_users ($uuid, admin_email, admin_password, admin_priviliges) VALUES (?, ?, ?, ?)";
$stmt = $mysqli->prepare($insert_sql);
$stmt->bind_param("ssss", $uuid, $email, $senha_hash, $privileges);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Usuário registrado com sucesso."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao registrar usuário."
    ]);
}
