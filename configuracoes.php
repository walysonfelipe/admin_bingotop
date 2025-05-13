<?php
session_start();
include 'php/connection.php';
require 'php/headerNav.php';

$sql = "SELECT * FROM configs";
$stmt = $mysqli->prepare($sql);
$result = $stmt->execute();
$response_data = $stmt->get_result();
$data = $response_data->fetch_assoc();

include_once 'php/functions.php';
$uuid = require_auth($mysqli);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Home</title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- CSS do switch -->
    <style>
        .switch {
            position: relative;
            display: block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #50C878;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #50C878;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php renderSidebarNavigation(); ?>
        <div id="tableWrapper">
            <div id="tableWrapperContent">
                <div id="wrapperTitle">
                    <h1>Configurações</h1>
                    <p>Altere as configurações da plataforma.</p>
                </div>
                <div id="wrapperContent">
                    <form id="configForm">
                        <h2 style="margin-bottom: 12px">Valor de depósito</h2>
                        <label htmlFor="">Valor mínimo para depósito:</label>
                        <input type="number" id="minDeposito" name="minDeposito" value="<?php echo ($data['valor_minimo_deposito']) ?>" placeholder="25,00" />
                        <h2 style="margin-bottom: 12px; margin-top: 12px">Valor dos jogos</h2>
                        <label htmlFor="">Valor padrão de entrada:</label>
                        <input type="number" id="minEntrada" name="minEntrada" value="<?php echo ($data['valor_entrada_padrao']) ?>" placeholder="100,00" />
                        <label htmlFor="">Valor padrão para o prêmio 1:</label>
                        <input type="number" id="padraoPremio1" name="padraoPremio1" value="<?php echo ($data['valor_premio_1_padrao']) ?>" placeholder="25,00" />
                        <label htmlFor="">Valor padrão para o prêmio 2:</label>
                        <input type="number" id="padraoPremio2" name="padraoPremio2" value="<?php echo ($data['valor_premio_2_padrao']) ?>" placeholder="100,00" />
                        <label htmlFor="">Valor padrão para o prêmio 3:</label>
                        <input type="number" id="padraoPremio3" name="padraoPremio3" value="<?php echo ($data['valor_premio_3_padrao']) ?>" placeholder="25,00" />
                        <h2 style="margin-bottom: 12px; margin-top: 12px">Configuração pop-up home</h2>
                        <label htmlFor="">Mensagem pop-up home:</label>
                        <textarea id="mensagemPopup" name="mensagemPopup" rows="6" cols="70" style="display: block; margin-top: 8px; padding: 6px"><?php echo ($data['popup_home_mensagem']); ?></textarea>
                        <h2 style="margin-bottom: 12px; margin-top: 12px">Configuração dos jogos</h2>
                        <label htmlFor="">Tempo padrão entre jogos (em minutos):</label>
                        <input type="number" id="tempoJogos" name="tempoJogos" value="<?php echo ($data['tempo_entre_jogos_padrao']) ?>" placeholder="100,00" />
                        <h2 style="margin-bottom: 12px; margin-top: 12px">Configuração dos depósitos</h2>
                        <label htmlFor="">Horário de bônus 2x está ativo:</label>
                        <label class="switch" style="margin-bottom: 14px;">
                            <input type="checkbox" id="bonusAtivo" <?php echo ($data['bonus_horario_ativo'] == true ? "checked" : "") ?>>
                            <span class="slider round"></span>
                        </label>
                        <label htmlFor="">Horário do início do bônus:</label>
                        <input type="time" id="tempoBonusInicio" name="tempoBonusInicio" value="<?php echo ($data['bonus_horario_inicio']) ?>" placeholder="00:00:00" step="1" />
                        <label htmlFor="">Horário do fim do bônus:</label>
                        <input type="time" id="tempoBonusFim" name="tempoBonusFim" value="<?php echo ($data['bonus_horario_fim']) ?>" placeholder="25,00" step="1" />
                        <label htmlFor="">Código de bônus para depósito triplicado:</label>
                        <input type="text" id="promoCode" name="promoCode" value="<?php echo ($data['codigo_bonus_deposito']) ?>" placeholder="ESTCODE" step="1" />
                        <button type="submit" id="saveBtn">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="js/config.js"></script>
</body>

</html>