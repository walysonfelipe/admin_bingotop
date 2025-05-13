<?php

include 'connection.php';

$charset = 'utf8mb4';


// Checa erro de conexão
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha na conexão: ' . $mysqli->connect_error]);
    exit;
}

// Define charset
if (! $mysqli->set_charset($charset)) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao definir charset: ' . $mysqli->error]);
    exit;
}

// Sua query (pode ler de arquivo .sql se preferir)
$sql = "
WITH filtered_users AS (
  SELECT id, nome, email, userSrc, userDataCadastro
  FROM users
  WHERE userSrc IN ('fb','facebook')
    AND userDataCadastro BETWEEN '2025-04-10' AND '2025-04-24'
),
user_values AS (
  SELECT
    u.id,
    COUNT(DISTINCT CASE WHEN t.status = 1 THEN t.transaction_id END) AS qtd_depositos,
    COALESCE(SUM(CASE WHEN t.status = 1 THEN t.quantia ELSE 0 END), 0) AS total_depositos,
    COUNT(DISTINCT CASE WHEN w.status = 1 THEN w.withdraw_id END) AS qtd_saques,
    COALESCE(SUM(CASE WHEN w.status = 1 THEN w.quantia ELSE 0 END), 0) AS total_saques
  FROM users u
  LEFT JOIN transactions t ON t.user_id = u.id AND t.transaction_date BETWEEN '2025-04-10' AND '2025-04-24'
  LEFT JOIN withdraws w ON w.user_id = u.id AND w.withdraw_datetime BETWEEN '2025-04-10' AND '2025-04-24'
  WHERE u.userSrc IN ('fb','facebook')
  GROUP BY u.id
),
summary AS (
  SELECT
    (SELECT COUNT(*) FROM users ux
      WHERE ux.userSrc IN ('fb','facebook')
        AND ux.userDataCadastro BETWEEN '2025-04-10' AND '2025-04-24') AS total_cadastros,
    (SELECT COUNT(DISTINCT tx.user_id) FROM transactions tx
      JOIN users ux ON ux.id = tx.user_id
      WHERE ux.userSrc IN ('fb','facebook')
        AND tx.status = 1
        AND tx.transaction_date BETWEEN '2025-04-10' AND '2025-04-24') AS total_depositantes,
    (SELECT COUNT(*) FROM transactions tx
      JOIN users ux ON ux.id = tx.user_id
      WHERE ux.userSrc IN ('fb','facebook')
        AND tx.status = 1
        AND tx.transaction_date BETWEEN '2025-04-10' AND '2025-04-24') AS total_depositos_aprovados,
    (SELECT COALESCE(SUM(tx.quantia),0) FROM transactions tx
      JOIN users ux ON ux.id = tx.user_id
      WHERE ux.userSrc IN ('fb','facebook')
        AND tx.status = 1
        AND tx.transaction_date BETWEEN '2025-04-10' AND '2025-04-24') AS valor_total_depositos_aprovados,
    (SELECT COALESCE(SUM(wx.quantia),0) FROM withdraws wx
      JOIN users ux ON ux.id = wx.user_id
      WHERE ux.userSrc IN ('fb','facebook')
        AND wx.status = 1
        AND wx.withdraw_datetime BETWEEN '2025-04-10' AND '2025-04-24') AS valor_total_saques_aprovados
)

SELECT
  fu.id,
  fu.nome,
  fu.email, 
  fu.userSrc,
  fu.userDataCadastro,
  NULL AS total_cadastros,
  NULL AS total_depositantes,
  uv.qtd_depositos,
  uv.total_depositos AS valor_total_depositos_aprovados,
  uv.qtd_saques,
  uv.total_saques AS valor_total_saques_aprovados
FROM filtered_users fu
LEFT JOIN user_values uv ON uv.id = fu.id

UNION ALL

SELECT
  NULL AS id,
  'TOTAIS' AS nome,
  NULL AS email,
  NULL AS userSrc,
  NULL AS userDataCadastro,
  s.total_cadastros,
  s.total_depositantes,
  s.total_depositos_aprovados,
  s.valor_total_depositos_aprovados,
  NULL AS qtd_saques,
  s.valor_total_saques_aprovados
FROM summary s;

";

// Executa query
if (! $result = $mysqli->query($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro na query: ' . $mysqli->error]);
    $mysqli->close();
    exit;
}

// Busca todos os registros como array associativo
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Libera resultado e fecha conexão
$result->free();
$mysqli->close();

// Retorna JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows);
