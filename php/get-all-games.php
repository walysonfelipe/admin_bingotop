<?php
ini_set ('display_errors', 1); 
ini_set ('display_startup_errors', 1); 
error_reporting (E_ALL);
include 'connection.php';

$today = date("Y-m-d");
$sql = "SELECT games.*
FROM games
WHERE games.data <= '". $today. "'
ORDER BY games.id DESC LIMIT 150";
$stmt = $mysqli->prepare($sql);
$result = $stmt->execute();
$response_data = $stmt->get_result();

$cards_sql = "SELECT count(purchased_tickets.id) 'total_tickets', purchased_tickets.id_sorteio
            FROM purchased_tickets
            INNER JOIN users
            ON purchased_tickets.id_usuario = users.id
            WHERE users.email <> '' or users.cpf <> '69218279092'
            GROUP BY purchased_tickets.id_sorteio
            ORDER BY purchased_tickets.id_sorteio DESC LIMIT 150";
$query = $mysqli->query($cards_sql);
$data_cards = $query->fetch_all(MYSQLI_ASSOC);

if($response_data->num_rows > 0) {
    $data = $response_data->fetch_all(MYSQLI_ASSOC);
    $html = "";

    foreach($data as $key => $row) {
        foreach($data_cards as $data_card) {
            if($row['id'] == $data_card['id_sorteio']) {
                $data[$key]['total_tickets'] = $data_card['total_tickets'];
            }
        }
    }

    foreach($data as $row) {
        if(!array_key_exists('total_tickets',$row)) {
            $row['total_tickets'] = 0;
        }
        $valor_arrecadado = $row['total_tickets'] * $row['entrada'];
        $valor_total = $row['premio1'] + $row['premio2'] + $row['premio3'];
        $html .= '
        <tr>
            <td>'. $row['id'] .'</td>
            <td>'. date("d/m/Y", strtotime($row['data'])) . " " . $row['hora'] .'</td>
            <td>'. $row['situacao'] .'</td>
            <td>R$'. number_format($valor_total, 2, ',', '.') .'</td>
            <td>'. $row['total_tickets'] .'</td>
            <td>R$'. number_format($valor_arrecadado, 2, ',', '.') .'</td>
            <td>R$'. number_format($valor_arrecadado - $valor_total, 2, ',', '.') .'</td>
        </tr>';
    }
    echo($html);
} else {
    echo('{"erro": "Sem depósitos realizados.", "code": 400}');
}
?>