<?php
session_start();

include_once 'php/connection.php';
include_once 'php/functions.php';
$uuid = require_auth($mysqli);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Exportar CSV</title>
</head>

<body>
    <button id="btn-export">Baixar CSV</button>

    <script>
        document.getElementById('btn-export').addEventListener('click', () => {
            fetch('php/csv_anuncios.php')
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        alert('Erro: ' + data.error);
                        return;
                    }

                    // Separar linha de totais
                    const totalRow = data.find(row => row.nome === 'TOTAIS');
                    const userRows = data.filter(row => row.nome !== 'TOTAIS');

                    const csv = toCSV(userRows, totalRow);
                    downloadCSV(csv, 'relatorio_usuarios.csv');
                })
                .catch(err => {
                    console.error(err);
                    alert('Falha ao buscar os dados.');
                });
        });

        // Converte array de objetos em CSV
        function toCSV(users, total) {
            if (!users.length && !total) return '';

            // Define campos que vão nas linhas dos usuários
            const userKeys = [
                'id',
                'nome',
                'email',
                'userSrc',
                'userDataCadastro',
                'qtd_depositos',
                'valor_total_depositos_aprovados',
                'qtd_saques',
                'valor_total_saques_aprovados'
            ];

            // Campos exclusivos da linha de totais
            const totalKeys = [
                'total_cadastros',
                'total_depositantes',
                'total_depositos_aprovados',
                'valor_total_depositos_aprovados',
                'valor_total_saques_aprovados'
            ];

            const lines = [];

            // Cabeçalho dos dados dos usuários
            lines.push(userKeys.join(','));

            // Linhas dos usuários
            for (const obj of users) {
                const row = userKeys.map(k => formatCSVCell(obj[k]));
                lines.push(row.join(','));
            }

            // Linha em branco antes dos totais
            lines.push('');

            // Cabeçalho da linha de totais
            lines.push(totalKeys.join(','));

            // Linha de totais
            if (total) {
                const totalRow = totalKeys.map(k => formatCSVCell(total[k]));
                lines.push(totalRow.join(','));
            }

            return lines.join('\r\n');
        }

        // Formata célula para CSV (escapa vírgulas, quebras de linha e aspas)
        function formatCSVCell(value) {
            let cell = value === null || value === undefined ? '' : value.toString();
            cell = cell.replace(/"/g, '""');
            if (cell.search(/,|\n/) >= 0) {
                cell = `"${cell}"`;
            }
            return cell;
        }

        // Gera download do CSV
        function downloadCSV(csvString, filename) {
            const blob = new Blob([csvString], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

</body>

</html>