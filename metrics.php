<?php
session_start();

include 'php/connection.php';
require 'php/headerNav.php';
include_once 'php/functions.php';


$uuid = require_auth($mysqli);

if (isset($_GET['dt_psq'])) {

    $get_metrics_sql = "SELECT u.userSrc, DATE(u.userDataCadastro) AS data_cadastro, COUNT(u.id) AS numero_usuarios, COALESCE(SUM(d.quantia), 0) AS total_depositado 
    FROM users u 
    LEFT JOIN transactions d ON u.id = d.user_id AND DATE(d.transaction_date) = DATE(u.userDataCadastro) 
    WHERE DATE(u.userDataCadastro) = '" . $_GET['dt_psq'] . "' AND d.status = 1 
    GROUP BY u.userSrc, DATE(u.userDataCadastro) 
    ORDER BY DATE(u.userDataCadastro) DESC";
} else {

    $get_metrics_sql = "SELECT u.userSrc, DATE(u.userDataCadastro) AS data_cadastro, COUNT(u.id) AS numero_usuarios, COALESCE(SUM(d.quantia), 0) AS total_depositado 
    FROM users u 
    LEFT JOIN transactions d ON u.id = d.user_id AND DATE(d.transaction_date) = DATE(u.userDataCadastro) 
    WHERE u.userDataCadastro >= NOW() - INTERVAL 7 DAY AND d.status = 1 
    GROUP BY u.userSrc, DATE(u.userDataCadastro) 
    ORDER BY DATE(u.userDataCadastro) DESC";
}

$stmt = $mysqli->prepare($get_metrics_sql);
$result = $stmt->execute();
$response_data = $stmt->get_result();
$data = $response_data->fetch_all(MYSQLI_ASSOC);

$original_values = [...$data];
$dates = [];
$src_data = []; // Array para armazenar dados por SRC

foreach ($data as $metric_row) {
    $date = date("d/m/Y", strtotime($metric_row['data_cadastro'])); // Formata a data
    $src = $metric_row['userSrc']; // SRC

    // Adiciona a data ao array de datas se não existir
    if (!in_array($date, $dates)) {
        $dates[] = $date;
    }

    // Inicializa o array para o SRC se não existir
    if (!isset($src_data[$src])) {
        $src_data[$src] = [
            'total_depositado' => array_fill(0, count($dates), 0), // Inicializa com zeros
            'numero_usuarios' => array_fill(0, count($dates), 0) // Inicializa com zeros
        ];
    }

    // Encontra o índice da data atual
    $date_index = array_search($date, $dates);

    // Adiciona os valores ao array do SRC
    $src_data[$src]['total_depositado'][$date_index] += (float)$metric_row['total_depositado'];
    $src_data[$src]['numero_usuarios'][$date_index] += (int)$metric_row['numero_usuarios'];
}

function randomColor()
{
    $r = rand(0, 255);
    $g = rand(0, 255);
    $b = rand(0, 255);
    return "rgba($r, $g, $b, 0.2)";
}

// Prepare os datasets para o Chart.js
$datasets_depositado = [];
$datasets_usuarios = [];
$colors = []; // Array para armazenar as cores

foreach ($src_data as $src => $data) {
    // Gera uma cor única para cada SRC
    $color = randomColor();
    $borderColor = str_replace('0.2', '1', $color); // Muda a opacidade para 1 para a borda

    $datasets_depositado[] = [
        'label' => $src . ' - Total Depositado',
        'data' => $data['total_depositado'],
        'backgroundColor' => $color,
        'borderColor' =>  $borderColor,
        'borderWidth' => 1,
        'yAxisID' => 'y'
    ];

    $datasets_usuarios[] = [
        'label' => $src . ' - Número de Usuários',
        'data' => $data['numero_usuarios'],
        'type' => 'line',
        'borderColor' => $borderColor,
        'backgroundColor' => $color,
        'fill' => false,
        'yAxisID' => 'y1'
    ];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Métricas</title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <style>
        #chartContainer {
            max-width: 40%;
            /* Largura máxima de 70% */
            max-height: 40%;
            /* Altura máxima de 70% */
            margin: 0 auto;
            /* Centraliza horizontalmente */
            padding: 24px;
            /* Padding de 12px */
            display: flex;
            /* Usado para centralizar verticalmente */
            justify-content: center;
            /* Centraliza horizontalmente */
            align-items: center;
            /* Centraliza verticalmente */
        }

        canvas {
            width: 100% !important;
            /* Faz o canvas ocupar 100% da largura do contêiner */
            height: 100% !important;
            /* Faz o canvas ocupar 100% da altura do contêiner */
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php renderSidebarNavigation(); ?>
        <div id="tableWrapper">
            <div id="tableWrapperContent">
                <div id="wrapperTitle">
                    <h1>Métricas</h1>
                    <p>Veja as métricas por cada SRC de cadastro.</p>
                </div>
                <div id="chartContainer">
                    <canvas id="myChart"></canvas>
                </div>
                <div id="wrapperContent" style="max-height: 400px">
                    <table id="contentTable">
                        <thead>
                            <tr>
                                <th>SRC de cadastro</th>
                                <th>Data de cadastro</th>
                                <th>Total de cadastros</th>
                                <th>Valor depositado aprovado</th>
                            </tr>
                        </thead>
                        <tbody id="depositTableBody">
                            <?php
                            foreach ($original_values as $metric_value) {
                            ?>
                                <tr>
                                    <td><?php echo ($metric_value['userSrc']); ?></td>
                                    <td><?php echo (date("d/m/Y", strtotime($metric_value['data_cadastro']))); ?></td>
                                    <td><?php echo ($metric_value['numero_usuarios']); ?></td>
                                    <td>R$<?php echo (number_format($metric_value['total_depositado'], 2, ",", ".")); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart');

        const mixedChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dates); ?>, // Datas como eixo X
                datasets: <?php echo json_encode(array_merge($datasets_depositado, $datasets_usuarios)); ?> // Conjuntos de dados para cada SRC
            },
            options: {
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',

                        // grid line settings
                        grid: {
                            drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                    },
                }
            }
        });

        $("#contentTable").DataTable({
            scrollY: 500,
            processing: true, // Mostra um indicador de carregamento
            language: {
                "decimal": ",",
                "thousands": ".",
                "sEmptyTable": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ resultados por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum registro encontrado",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    </script>
</body>

</html>