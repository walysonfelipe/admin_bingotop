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
    'codigo', // 4
    'saldo', // 5
    'saldo_bonus', // 6
    'userDataCadastro', // 7
    'userSrc', // 8
    'depositos_realizados', // 9
    'depositos_concluidos', // 10
    'total_depositado', // 11
    'total_premios', // 12
    'lucro', // 13
];

// A coluna de ordenação (verifica o índice da coluna que o DataTables enviou)
$orderBy = isset($orderColumns[$orderColumnIndex]) ? $orderColumns[$orderColumnIndex] : 'nome';

// Verifica se os parâmetros start e length são válidos
if ($start < 0 || $length <= 0) {
    die('Parâmetros inválidos de paginação');
}

// Criar a consulta base
$sql = "SELECT * FROM users WHERE (email <> '' OR cpf <> '69218279092')";

// Se houver um termo de pesquisa, adicionamos uma cláusula WHERE
if ($search) {
    $sql .= " AND (nome LIKE ? OR cpf LIKE ? OR email LIKE ? OR telefone LIKE ? OR userSrc LIKE ?)";
}

// Adiciona LIMIT e OFFSET para paginar os resultados
$sql .= " ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";

// Prepara a consulta
$stmt = $mysqli->prepare($sql);

// Se houver pesquisa, bind os parâmetros para a consulta
if ($search) {
    $searchTerm = "%$search%";
    $stmt->bind_param("sssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $length, $start);
} else {
    $stmt->bind_param("ii", $length, $start);
}

$stmt->execute();
$response_data = $stmt->get_result();

// Contar o total de registros
$totalRecordsSql = "SELECT COUNT(*) as total FROM users WHERE (email <> '' OR cpf <> '69218279092')";
$totalRecordsResult = $mysqli->query($totalRecordsSql);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Contar o total de registros após a pesquisa
if ($search) {
    $filteredRecordsSql = "SELECT COUNT(*) as total FROM users WHERE (email <> '' OR cpf <> '69218279092') AND (nome LIKE ? OR cpf LIKE ? OR email LIKE ?)";
    $filteredStmt = $mysqli->prepare($filteredRecordsSql);
    $filteredStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $filteredStmt->execute();
    $filteredRecordsResult = $filteredStmt->get_result();
    $filteredRecords = $filteredRecordsResult->fetch_assoc()['total'];
} else {
    $filteredRecords = $totalRecords;
}

// Obter os dados da página atual
$data = $response_data->fetch_all(MYSQLI_ASSOC);

// Adicionar dados adicionais (exemplo de depósitos e prêmios)
$deposits_sql = "SELECT user_id, status, quantia FROM transactions";
$prizes_sql = "SELECT id_user, valor_premio FROM users_prizes";

$deposits_data = $mysqli->query($deposits_sql)->fetch_all(MYSQLI_ASSOC);
$prizes_data = $mysqli->query($prizes_sql)->fetch_all(MYSQLI_ASSOC);

$deposits_data_per_userid = [];

foreach ($deposits_data as $deposit) {
    $user_id = $deposit['user_id'];
    if (!isset($deposits_data_per_userid[$user_id])) {
        $deposits_data_per_userid[$user_id] = [
            'depositos_realizados' => 0,
            'depositos_concluidos' => 0,
            'total_depositado' => 0.0,
            'total_premios' => 0.0,
        ];
    }
    $deposits_data_per_userid[$user_id]['depositos_realizados']++;
    if ($deposit['status'] == 1) {
        $deposits_data_per_userid[$user_id]['depositos_concluidos']++;
        $deposits_data_per_userid[$user_id]['total_depositado'] += $deposit['quantia'];
    }
}

foreach ($prizes_data as $prize) {
    $user_id = $prize['id_user'];
    if (isset($deposits_data_per_userid[$user_id])) {
        $deposits_data_per_userid[$user_id]['total_premios'] += $prize['valor_premio'];
    }
}

// Criar a resposta no formato adequado para o DataTables
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => []
];

foreach ($data as $row) {
    $dataNasc = $row['userDataCadastro'] ? date("d/m/Y H:i:s", strtotime($row['userDataCadastro'])) : "-";
    $response['data'][] = [
        'nome' => $row['nome'],
        'cpf' => $row['cpf'],
        'email' => $row['email'],
        'telefone' => $row['telefone'],
        'codigo' => $row['codigo'],
        'saldo' => 'R$' . number_format($row['saldo'], 2, ",", "."),
        'saldo_bonus' => 'R$' . number_format($row['saldo_bonus'], 2, ",", "."),
        'data_cadastro' => $dataNasc,
        'userSrc' => $row['userSrc'],
        'depositos_realizados' => isset($deposits_data_per_userid[$row['id']]) ? $deposits_data_per_userid[$row['id']]['depositos_realizados'] : 0,
        'depositos_concluidos' => isset($deposits_data_per_userid[$row['id']]) ? $deposits_data_per_userid[$row['id']]['depositos_concluidos'] : 0,
        'total_depositado' => isset($deposits_data_per_userid[$row['id']]) ? 'R$' . number_format($deposits_data_per_userid[$row['id']]['total_depositado'], 2, ",", ".") : 'R$0,00',
        'total_premios' => isset($deposits_data_per_userid[$row['id']]) ? 'R$' . number_format($deposits_data_per_userid[$row['id']]['total_premios'], 2, ",", ".") : 'R$0,00',
        'lucro' => isset($deposits_data_per_userid[$row['id']]) ? 'R$' . number_format($deposits_data_per_userid[$row['id']]['total_premios'] - $deposits_data_per_userid[$row['id']]['total_depositado'], 2, ",", ".") : 'R$0,00',
        'editar' => '<button type="button" class="editBtn" data-userid="' . $row['id'] . '">📝</button>',
    ];
}

// Enviar a resposta no formato JSON
echo json_encode($response);
?>