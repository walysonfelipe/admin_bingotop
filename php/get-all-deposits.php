<?php
include 'connection.php';

// Recebe os parâmetros via POST
$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;  // Posição inicial para a paginação
$length = isset($_POST['length']) ? (int) $_POST['length'] : 10;  // Número de resultados por página
$draw = isset($_POST['draw']) ? (int) $_POST['draw'] : 1; // O parâmetro draw enviado pelo DataTable
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';  // Termo de pesquisa

// Recebe os parâmetros de ordenação da requisição do DataTables
$orderColumnIndex = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Mapeia os índices das colunas para os nomes das colunas do banco de dados
$orderColumns = [
    'nome', // 0
    'cpf', // 1
    'email', // 2
    'telefone', // 3
    'transaction_date', // 4
    'quantia', // 5
    'status', // 6
    'transaction_src'
];

// A coluna de ordenação (verifica o índice da coluna que o DataTables enviou)
$orderBy = isset($orderColumns[$orderColumnIndex]) ? $orderColumns[$orderColumnIndex] : 'transaction_date';

// Verifica se os parâmetros start e length são válidos
if ($start < 0 || $length <= 0) {
    die('Parâmetros inválidos de paginação');
}

// Criar a consulta base
$sql = "SELECT transactions.*, users.email, users.telefone 
FROM transactions 
INNER JOIN users
ON transactions.user_id = users.id";

// Se houver um termo de pesquisa, adicionamos uma cláusula WHERE
if ($search) {
    $sql .= " WHERE nome LIKE ? OR cpf LIKE ? OR email LIKE ? OR telefone LIKE ? OR transactions.transaction_date LIKE ? OR   transactions.transaction_src LIKE ?";
}

// Adiciona LIMIT e OFFSET para paginar os resultados
$sql .= " ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";

// Prepara a consulta
$stmt = $mysqli->prepare($sql);

// Se houver pesquisa, bind os parâmetros para a consulta
if ($search) {
    $searchTerm = "%$search%";
    $stmt->bind_param("ssssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $length, $start);
} else {
    $stmt->bind_param("ii", $length, $start);
}

$stmt->execute();
$response_data = $stmt->get_result();

// Contar o total de registros
$totalRecordsSql = "SELECT COUNT(transactions.transaction_id) as total
FROM transactions 
INNER JOIN users
ON transactions.user_id = users.id";
$totalRecordsResult = $mysqli->query($totalRecordsSql);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Contar o total de registros após a pesquisa
if ($search) {
    $filteredRecordsSql = "SELECT COUNT(transactions.transaction_id) as total
    FROM transactions 
    INNER JOIN users
    ON transactions.user_id = users.id WHERE (nome LIKE ? OR cpf LIKE ? OR email LIKE ?) OR transactions.transaction_src LIKE ?";
    $filteredStmt = $mysqli->prepare($filteredRecordsSql);
    $filteredStmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $filteredStmt->execute();
    $filteredRecordsResult = $filteredStmt->get_result();
    $filteredRecords = $filteredRecordsResult->fetch_assoc()['total'];
} else {
    $filteredRecords = $totalRecords;
}

// Obter os dados da página atual
$data = $response_data->fetch_all(MYSQLI_ASSOC);

// Criar a resposta no formato adequado para o DataTables
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => []
];

foreach ($data as $row) {
    $dataNasc = $row['transaction_date'] ? date("d/m/Y H:i:s", strtotime($row['transaction_date'])) : "-";
    if ($row['quantia'] < 25) {
        $bonus = $row['quantia'] * 1;
    } else if ($row['quantia'] < 50) {
        $bonus = $row['quantia'] * 1.5;
    } else if ($row['quantia'] >= 50) {
        $bonus = $row['quantia'] * 2;
    }

    if ($row['status'] == 0) {
        $status = "Aguardando";
    } else if ($row['status'] == 1) {
        $status = "Aprovado";
    } else {
        $status = "Cancelado";
    }
    $response['data'][] = [
        'nome' => $row['transaction_name'],
        'cpf' => substr_replace($row['transaction_cpf'], '****', -4),
        'email' => $row['email'],
        'telefone' => $row['telefone'],
        'transaction_date' => $dataNasc,
        'quantia' => 'R$' . number_format($row['quantia'], 2, ",", "."),
        'saldo_bonus' => 'R$' . number_format($bonus, 2, ",", "."),
        'transaction_src' => $row['transaction_src'],
        'status' => $status,
    ];
}

// Enviar a resposta no formato JSON
echo json_encode($response);
