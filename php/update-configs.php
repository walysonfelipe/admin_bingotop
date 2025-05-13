<?php
include 'connection.php';

include_once 'functions.php';

// Valida o uuid para isso
if (empty($_COOKIE['session'])) {
    error_log("Sessão não encontrada: cookie 'session' ausente ou vazio.");
    header('HTTP/1.1 401 Unauthorized');
    exit("Error: Session cookie not found.");
}

$uuid = validate_uuid($mysqli, $_COOKIE['session']);


$minDeposito = $_POST['minDeposito'];
$minEntrada = $_POST['minEntrada'];
$padraoPremio1 = $_POST['padraoPremio1'];
$padraoPremio2 = $_POST['padraoPremio2'];
$padraoPremio3 = $_POST['padraoPremio3'];
$mensagemPopup = $_POST['mensagemPopup'];
$tempoJogos = $_POST['tempoJogos'];
$bonusAtivo = boolval($_POST['bonusAtivo']);
$tempoBonusInicio = $_POST['tempoBonusInicio'];
$tempoBonusFim = $_POST['tempoBonusFim'];
$promoCode = $_POST['promoCode'];

$sql = "UPDATE configs SET 	valor_minimo_deposito = ?, valor_premio_1_padrao = ?, valor_premio_2_padrao = ?, valor_premio_3_padrao = ?, popup_home_mensagem = ?, valor_entrada_padrao = ?, tempo_entre_jogos_padrao = ?, bonus_horario_ativo = ?, bonus_horario_inicio = ?, bonus_horario_fim = ?, codigo_bonus_deposito = ? WHERE 1=1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "idddsdiisss",
    $minDeposito,
    $padraoPremio1,
    $padraoPremio2,
    $padraoPremio3,
    $mensagemPopup,
    $minEntrada,
    $tempoJogos,
    $bonusAtivo,
    $tempoBonusInicio,
    $tempoBonusFim,
    $promoCode
);
$result = $stmt->execute();
if ($result) {
    echo ('{"message": "Atualizado com sucesso!", "code": 200}');

    // Adiciona o registro disso no banco de dados.
    $action = "Alteração nas configurações na plataforma de admin. Dados: UUID: " . $uuid;
    $location = "php/update-configs.php";
    $insert_log_sql = "INSERT INTO admin_history VALUES (DEFAULT, ?, ?, ?, NOW())";
    $insert_log_stmt = $mysqli->prepare($insert_log_sql);
    $insert_log_stmt->bind_param(
        "sss",
        $uuid,
        $action,
        $location
    );
    $insert_log_stmt->execute();
    $insert_log_stmt->close();
} else {
    echo ('{"erro": "Sem depósitos realizados.", "code": 400}');
}
