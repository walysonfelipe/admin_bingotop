<?php 
include 'connection.php';

$sql = "SELECT * FROM users WHERE email <> '' and cpf <> '69218279092'";
$query = $mysqli->query($sql);
$all_data = $query->fetch_all(MYSQLI_ASSOC);

// Vamos passar por cada usuário
foreach($all_data as $user) {
    $cpf_clean = preg_replace('/[^0-9]/', '', $user['cpf']);
    $telefone_clean = preg_replace('/[^0-9]/', '', $user['telefone']);

    $update_sql = "UPDATE users SET cpf = $cpf_clean, telefone = $telefone_clean WHERE id = " . $user['id'];
    $query = $mysqli->query($update_sql);
}

?>