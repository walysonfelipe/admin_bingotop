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
    <title>Admin - Jogos</title>
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
                    <h1>Jogos</h1>
                    <p>Verifique o histórico de jogos da plataforma.</p>
                </div>
                <div id="wrapperContent">
                    <table id="contentTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data e hora</th>
                                <th>Status do jogo</th>
                                <th>Valor premiação</th>
                                <th>Qtd. tickets</th>
                                <th>Valor arrecadado</th>
                                <th>Balanço</th>
                            </tr>
                        </thead>
                        <tbody id="gamesTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="js/games.js"></script>
</body>

</html>