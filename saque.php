<?php
session_start();

$session_id = $_COOKIE['session'];
include_once 'php/connection.php';
include_once 'php/functions.php';
require 'php/headerNav.php';

$uuid = require_auth($mysqli, $session_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Saque</title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
    <div class="wrapper">
        <?php renderSidebarNavigation(); ?>
        <div id="tableWrapper">
            <div id="tableWrapperContent">
                <div id="wrapperTitle">
                    <h1>Saques via SuitPay</h1>
                    <p>Libere ou rejeite solicitações de saques de usuários.</p>
                </div>
                <div id="wrapperContent">
                    <table id="contentTable">
                        <thead>
                            <tr>
                                <th>Nome completo</th>
                                <th>CPF</th>
                                <th>E-mail</th>
                                <th>Tel.</th>
                                <th>Valor do saque</th>
                                <th>Tipo de chave</th>
                                <th>Chave PIX</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="withdrawTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="js/withdrawn.js"></script>
</body>

</html>