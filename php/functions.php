<?php

function require_auth($mysqli, $admin = null)
{
    if (!isset($_COOKIE['session']) && $admin = null) {
        // Se não há cookie de sessão, redireciona para o login
        header("Location: index.php");
        exit;
    }

    $session_id = $_COOKIE['session'];

    // Verifica se o session_id está na tabela de sessões e se ainda não expirou
    $stmt = $mysqli->prepare("SELECT uuid_admin, expires_at FROM sessions WHERE session_id = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Se a sessão estiver válida, retorna o uuid_admin
        return $row['uuid_admin'];
    } else {
        // Se a sessão expirou ou não existe, remove o cookie e redireciona para o login
        logout($mysqli); // Chama a função de logout para garantir que o cookie seja removido
        header("Location: index.php");
        exit;
    }
}


function validate_uuid($mysqli, $session_id)
{


    // Verifica se o session_id está na tabela de sessões e se ainda não expirou
    $stmt = $mysqli->prepare("SELECT uuid_admin, expires_at FROM sessions WHERE session_id = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Se a sessão estiver válida, retorna o uuid_admin
        return $row['uuid_admin'];
    } else {
        return null;
    }
}

function logout($mysqli)
{
    // Verifica se existe o cookie de sessão
    if (isset($_COOKIE['session'])) {
        // Obtém o session_id do cookie
        $session_id = $_COOKIE['session'];

        // Remove a sessão do banco de dados
        $stmt = $mysqli->prepare("DELETE FROM sessions WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();

        // Remove o cookie de sessão
        setcookie("session", "", time() - 3600, "/", "", false, true);  // Define a data de expiração no passado

        // Opcional: Remove o cookie de privilégios
        setcookie("privileges", "", time() - 3600, "/");
    } else {
        // Se não houver cookie de sessão, redireciona diretamente para a página de login
        header("Location: index.php");
        exit;
    }
}
