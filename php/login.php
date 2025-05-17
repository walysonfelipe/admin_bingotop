<?php
session_start();
header('Content-Type: application/json');

$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? null;

include_once 'connection.php';

if (!$email || !$senha) {
    echo json_encode([
        "success" => false,
        "message" => "Email ou senha não enviados.",
        "debug" => [
            "email" => $email,
            "senha" => $senha
        ]
    ]);
    exit;
}

// Buscar usuário no banco de dados
$login_sql = "SELECT * FROM admin_users WHERE admin_email = ?";
$stmt = $mysqli->prepare($login_sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Erro no Banco de Dados",
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$response_result = $stmt->get_result();

if ($response_result->num_rows == 1) {
    $response_data = $response_result->fetch_assoc();

    if (strlen($response_data['admin_password']) == 32) { // hash md5
        if (md5($senha) == $response_data['admin_password']) {
            $new_hashed_password = password_hash($senha, PASSWORD_BCRYPT);

            $update_sql = "UPDATE admin_users SET admin_password = ? WHERE admin_email = ?";
            $stmt_update = $mysqli->prepare($update_sql);

            if (!$stmt_update) {
                echo json_encode([
                    "success" => false,
                    "message" => "Erro no Banco de Dados"
                ]);
                exit;
            }

            $stmt_update->bind_param("ss", $new_hashed_password, $email);
            $stmt_update->execute();
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Erro no Banco de Dados"
            ]);
            exit;
        }
    }

    if (password_verify($senha, $response_data['admin_password'])) {
        $session_id = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $mysqli->prepare("SELECT 1 FROM sessions WHERE uuid_admin = ? LIMIT 1");
        $stmt->bind_param("s", $response_data['uid']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt = $mysqli->prepare("UPDATE sessions SET session_id = ?, expires_at = ? WHERE uuid_admin = ?");
            $stmt->bind_param("sss", $session_id, $expires_at, $response_data['uid']);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO sessions (session_id, uuid_admin, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $session_id, $response_data['uid'], $expires_at);
        }

        if (!$stmt) {
            echo json_encode([
                "success" => false,
                "message" => "Erro no Banco de Dados"
            ]);
            exit;
        }

        $stmt->execute();

        setcookie("session", $session_id, [
            'expires' => time() + 1200,
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);

        setcookie("privileges", $response_data['admin_priviliges'], time() + 86400, "/");

        $action = "Login na plataforma de admin";
        $location = "php/login.php";
        $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
        $insert_log_stmt = $mysqli->prepare($insert_log_sql);

        if (!$insert_log_stmt) {
            echo json_encode([
                "success" => false,
                "message" => "Erro no Banco de Dados"
            ]);
            exit;
        }

        $insert_log_stmt->bind_param("sss", $response_data['uid'], $action, $location);
        $insert_log_stmt->execute();
        $insert_log_stmt->close();

        echo json_encode([
            "success" => true,
            "message" => "Login efetuado com sucesso."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Erro no Banco de Dados"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Usuário não encontrado."
    ]);
}
