<?php

ini_set ('display_errors', 1); 
ini_set ('display_startup_errors', 1); 
error_reporting (E_ALL);
include 'connection.php';

$get_metrics_sql = "SELECT 
    u.userSrc, 
    DATE(u.userDataCadastro) AS data_cadastro, 
    COUNT(u.id) AS numero_usuarios, 
    COALESCE(SUM(d.quantia), 0) AS total_depositado,
    COUNT(DISTINCT d.user_id) AS usuarios_unicos_depositaram,
    COUNT(DISTINCT CASE WHEN d.status = 1 THEN d.user_id END) AS usuarios_unicos_pagaram
FROM 
    users u 
LEFT JOIN 
    transactions d ON u.id = d.user_id AND DATE(d.transaction_date) = DATE(u.userDataCadastro) 
WHERE 
    u.userDataCadastro >= NOW() - INTERVAL 7 DAY 
GROUP BY 
    u.userSrc, DATE(u.userDataCadastro) 
ORDER BY 
    DATE(u.userDataCadastro);";

$stmt = $mysqli->prepare($sql);
$result = $stmt->execute();
$response_data = $stmt->get_result();
if($response_data->num_rows > 0) {
    $data = $response_data->fetch_all(MYSQLI_ASSOC);
    $html = "";
    foreach($data as $row) {
        $html .= '
        <tr>
            <td>'. $row['userSrc'] .'</td>
            <td>'. date("d/m/Y", strtotime($row['data_cadastro'])) .'</td>
            <td>'. $row['numero_usuarios'] .'</td>
            <td>'. $row['usuarios_unicos_depositaram'] .'</td>
            <td>'. $row['usuarios_unicos_pagaram'] .'</td>
            <td>R$'. number_format($row['total_depositado'], 2, ",", ".") .'</td>
        </tr>';
    }
    echo($html);
}
?>