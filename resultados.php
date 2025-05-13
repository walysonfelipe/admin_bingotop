<?php
session_start();

include 'php/connection.php';
require 'php/headerNav.php';
include_once 'php/functions.php';
$uuid = require_auth($mysqli);

// Array para armazenar os dados combinados
$dados = [];

// Query para total de depósitos pagos por dia
$sqlDepositos = "SELECT DATE(transaction_date) AS dia, SUM(quantia) AS total_depositos 
                 FROM transactions WHERE status = 1 AND DATE(transaction_date) >= CURDATE() - INTERVAL 30 DAY
                 GROUP BY dia ORDER BY dia ASC";
$result = $mysqli->query($sqlDepositos);
while ($row = $result->fetch_assoc()) {
    $dados[$row['dia']]['total_depositos'] = $row['total_depositos'];
}

// Query para total de saques aprovados por dia
$sqlSaques = "SELECT DATE(withdraw_datetime) AS dia, SUM(quantia) AS total_saques 
              FROM withdraws WHERE status = 1 AND DATE(withdraw_datetime) >= CURDATE() - INTERVAL 30 DAY
              GROUP BY dia ORDER BY dia ASC";
$result = $mysqli->query($sqlSaques);
while ($row = $result->fetch_assoc()) {
    $dados[$row['dia']]['total_saques'] = $row['total_saques'];
}

// Query para novos usuários cadastrados por dia
$sqlUsuarios = "SELECT DATE(userDataCadastro) AS dia, COUNT(id) AS novos_usuarios 
                FROM users WHERE DATE(userDataCadastro) >= CURDATE() - INTERVAL 30 DAY AND email 
                IS NOT NULL AND email != '' GROUP BY dia ORDER BY dia ASC";
$result = $mysqli->query($sqlUsuarios);
while ($row = $result->fetch_assoc()) {
    $dados[$row['dia']]['novos_usuarios'] = $row['novos_usuarios'];
}

// Preparar dados para o gráfico
$labels = [];
$depositos = [];
$saques = [];

foreach ($dados as $dia => $valores) {
    $labels[] = date("d/m/Y", strtotime($dia));
    $depositos[] = $valores['total_depositos'] ?? 0;
    $saques[] = $valores['total_saques'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Resultados diários</title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" type="text/css" href="css/popup.css">
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
                    <h1>Resultados diários</h1>
                    <p>Veja os resultados macros por dia.</p>
                </div>
                <div id="chartContainer">
                    <canvas id="myChart"></canvas>
                </div>
                <div id="wrapperContent" style="max-height: 400px">
                    <table id="contentTable">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Total de usuários cadastrados</th>
                                <th>Total em depósitos aprovados</th>
                                <th>Total em saques aprovados</th>
                                <th>Subtotal diário</th>
                            </tr>
                        </thead>
                        <tbody id="depositTableBody">
                            <?php
                            foreach ($dados as $dia => $valores) {
                                $total_depositos = $valores['total_depositos'] ?? 0;
                                $total_saques = $valores['total_saques'] ?? 0;
                                $novos_usuarios = $valores['novos_usuarios'] ?? 0;
                                $subtotal = $total_depositos - $total_saques;

                                echo "<tr onclick='window.location.href=\"metrics.php?dt_psq=" . $dia . "\"'>
                                        <td data-sort='YYYYMMDD'>" . date("d/m/Y", strtotime($dia)) . "</td>
                                        <td>" . $novos_usuarios . "</td>
                                        <td>R$" . number_format($total_depositos, 2, ',', '.') . "</td>
                                        <td>R$" . number_format($total_saques, 2, ',', '.') . "</td>
                                        <td>R$" . number_format($subtotal, 2, ',', '.') . "</td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                        label: 'Depósitos',
                        data: <?php echo json_encode($depositos); ?>,
                        borderColor: 'blue',
                        fill: false
                    },
                    {
                        label: 'Saques',
                        data: <?php echo json_encode($saques); ?>,
                        borderColor: 'red',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Data'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Valores'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2
                                });
                            }
                        }
                    }
                }
            }
        });

        $("#contentTable").DataTable({
            scrollY: 500,
            processing: true, // Mostra um indicador de carregamento
            order: [
                [0, 'desc']
            ],
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