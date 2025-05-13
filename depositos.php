<?php
session_start();

include_once 'php/connection.php';
include_once 'php/functions.php';
require 'php/headerNav.php';
$uuid = require_auth($mysqli);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Depósitos</title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
</head>

<body>
    <div class="wrapper">
        <?php renderSidebarNavigation(); ?>
        <div id="tableWrapper">
            <div id="tableWrapperContent">
                <div id="wrapperTitle">
                    <h1>Depósitos</h1>
                    <p>Veja todos os depósitos feitos na plataforma.</p>
                </div>
                <div id="wrapperContent">
                    <table id="contentTable">
                        <thead>
                            <tr>
                                <th>Nome completo</th>
                                <th>CPF</th>
                                <th>E-mail</th>
                                <th>Tel.</th>
                                <th>Data do depósito</th>
                                <th>Valor do depósito</th>
                                <th>Bônus recebido</th>
                                <th>Origem</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="depositTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="js/deposit.js"></script>
</body>

</html>