<?php
ini_set ('display_errors', 1); 
ini_set ('display_startup_errors', 1); 
error_reporting (E_ALL);
include 'connection.php';

$sql = "SELECT withdraws.*, users.email, users.telefone 
FROM withdraws 
INNER JOIN users
ON withdraws.user_id = users.id
WHERE status = 0";
$stmt = $mysqli->prepare($sql);
$result = $stmt->execute();
$response_data = $stmt->get_result();
if($response_data->num_rows > 0) {
    $data = $response_data->fetch_all(MYSQLI_ASSOC);
    $html = "";
    foreach($data as $row) {
        $html .= '
        <tr>
            <td>'. $row['withdraw_nome'] .'</td>
            <td>'. $row['withdraw_cpf'] .'</td>
            <td>'. $row['email'] .'</td>
            <td>'. $row['telefone'] .'</td>
            <td>R$'. number_format($row['quantia'], 2, ",", ".") .'</td>
            <td>'. $row['withdraw_pixtype'] .'</td>
            <td>'. $row['withdraw_pixkey'] .'</td>
            <td><button class="accept process_withdraw" data-type="accept" data-withdrawid="'. $row['withdraw_id'] .'">Aprovar</button></td>
            <td><button class="deny process_withdraw" data-type="deny" data-withdrawid="'. $row['withdraw_id'] .'">Recusar</button></td>
        </tr>';
    }
    echo($html);
} else {
    echo('{"erro": "Sem saques solicitados.", "code": 400}');
}
?>