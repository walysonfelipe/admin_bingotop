<?php
ini_set ('display_errors', 1); 
ini_set ('display_startup_errors', 1); 
error_reporting (E_ALL);
include 'connection.php';

$sql = "SELECT count(purchased_tickets.id) 'total_tickets', users.nome, users.email, purchased_tickets.id_sorteio, purchased_tickets.id_usuario, games.entrada
FROM purchased_tickets
INNER JOIN users
ON purchased_tickets.id_usuario = users.id
INNER JOIN games
ON games.id = purchased_tickets.id_sorteio
WHERE users.email <> '' or users.cpf <> '69218279092'
GROUP BY purchased_tickets.id_usuario, purchased_tickets.id_sorteio
ORDER BY purchased_tickets.id_sorteio DESC LIMIT 100";
$stmt = $mysqli->prepare($sql);
$result = $stmt->execute();
$response_data = $stmt->get_result();
if($response_data->num_rows > 0) {
    $data = $response_data->fetch_all(MYSQLI_ASSOC);
    $html = "";
    foreach($data as $row) {
        $valor_apostado = $row['total_tickets'] * $row['entrada'];
        $html .= '
        <tr>
            <td>'. $row['nome'] .'</td>
            <td>'. $row['email'] .'</td>
            <td>'. $row['total_tickets'] .'</td>
            <td>'. $row['id_sorteio'] .'</td>
            <td>R$'. number_format($valor_apostado, 2, ',', '.') .'</td>
        </tr>';
    }
    echo($html);
} else {
    echo('{"erro": "Sem depósitos realizados.", "code": 400}');
}
?>