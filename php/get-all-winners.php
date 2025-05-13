<?php
include 'connection.php';

$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
$draw = isset($_POST['draw']) ? (int) $_POST['draw'] : 1;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

$orderColumnIndex = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

$orderColumns = [
    'users.nome',          // 0
    'users.cpf',           // 1
    'users.email',         // 2
    'users.telefone',      // 3
    'users_prizes.id_game',// 4
    'users_prizes.id_card',// 5
    'users_prizes.premio_ganho', // 6
    'users_prizes.valor_premio', // 7
    'games.data',          // 8
];

$orderBy = isset($orderColumns[$orderColumnIndex]) ? $orderColumns[$orderColumnIndex] : 'games.data';

if ($start < 0 || $length <= 0) {
    die('Parâmetros inválidos de paginação');
}

$sql = "SELECT users_prizes.*, users.email, users.nome, users.cpf, users.telefone, games.data, games.hora
FROM users_prizes
INNER JOIN users ON users_prizes.id_user = users.id
INNER JOIN games ON users_prizes.id_game = games.id
WHERE (users.email <> '' OR users.cpf <> '69218279092')";

if ($search) {
    $sql .= " AND (users.nome LIKE ? OR users.cpf LIKE ? OR users.email LIKE ? OR users.telefone LIKE ? OR users_prizes.premio_ganho LIKE ?)";
}

$sql .= " ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($sql);

if ($search) {
    $searchTerm = "%$search%";
    $stmt->bind_param("sssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $length, $start);
} else {
    $stmt->bind_param("ii", $length, $start);
}

$stmt->execute();
$response_data = $stmt->get_result();

// Total sem filtro
$totalSql = "SELECT COUNT(*) as total FROM users_prizes
INNER JOIN users ON users_prizes.id_user = users.id
WHERE (users.email <> '' OR users.cpf <> '69218279092')";
$totalResult = $mysqli->query($totalSql);
$totalRecords = $totalResult->fetch_assoc()['total'];

// Total com filtro
if ($search) {
    $countFilteredSql = "SELECT COUNT(*) as total FROM users_prizes
    INNER JOIN users ON users_prizes.id_user = users.id
    INNER JOIN games ON users_prizes.id_game = games.id
    WHERE (users.email <> '' OR users.cpf <> '69218279092')
    AND (users.nome LIKE ? OR users.cpf LIKE ? OR users.email LIKE ? OR users.telefone LIKE ? OR users_prizes.premio_ganho LIKE ?)";
    $countStmt = $mysqli->prepare($countFilteredSql);
    $countStmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $filteredRecords = $countResult->fetch_assoc()['total'];
} else {
    $filteredRecords = $totalRecords;
}

$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => []
];

while ($row = $response_data->fetch_assoc()) {
    $premio = match ($row['premio_ganho']) {
        'premio_1' => 'Prêmio 1',
        'premio_2' => 'Prêmio 2',
        default => 'Prêmio 3',
    };
    $dataCompleta = $row['data'] ? date("Y-m-d H:i:s", strtotime($row['data'] . " " . $row['hora'])) : "-";
    $response['data'][] = [
        'nome' => $row['nome'],
        'cpf' => $row['cpf'],
        'email' => $row['email'],
        'telefone' => $row['telefone'],
        'id_game' => $row['id_game'],
        'id_card' => $row['id_card'],
        'premio_ganho' => $premio,
        'valor_premio' => $row['valor_premio'],
        'data' => $dataCompleta,
    ];
}

echo json_encode($response);
?>
