<?php
include 'connection.php';

$uid = $_POST['uid'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "i", $uid
);
$stmt->execute();
$response_data = $stmt->get_result();

if ($response_data->num_rows > 0) {
    $data = $response_data->fetch_all(MYSQLI_ASSOC);
    
    // Obtém todos os depósitos e prêmios de uma vez
    $deposits_sql = "SELECT user_id, status, quantia FROM transactions WHERE user_id = ". $data[0]['id'];
    $prizes_sql = "SELECT id_user, valor_premio FROM users_prizes WHERE id_user = ". $data[0]['id'];
    
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
    
    foreach($data as $row) {
        $dataNasc = $row['userDataCadastro'] ? date("d/m/Y H:i:s", strtotime($row['userDataCadastro'])) : "-";
        $html = '
        <div class="popupBody">
            <div class="popupHeader">
                <h2>Editar usuário</h2>
                <i class="fa-solid fa-times" id="closePopup"></i>
            </div>
            <div class="popupContent">
                <input type="hidden" name="uid" id="uid" value="'. $row['id'] .'">
                <label>Nome do usuário:</label>
                <input type="text" name="nome" id="nome" value="'. $row['nome'] .'">
                <label>CPF:</label>
                <input type="text" name="cpf" id="cpf" readonly value="'. substr_replace($row['cpf'], '****', -4) .'">
                <label>E-mail:</label>
                <input type="text" name="email" id="email" value="'. $row['email'] .'">
                <label>Tefelone:</label>
                <input type="text" name="telefone" id="telefone" value="'. $row['telefone'] .'">
                <label>Código de acesso:</label>
                <input type="text" name="codigo" id="codigo" value="'. $row['codigo'] .'">
                <label>Saldo:</label>
                <input type="number" name="saldo" id="saldo" value="'. $row['saldo'] .'">
                <label>Saldo bônus:</label>
                <input type="number" name="saldoBonus" id="saldoBonus" value="'. $row['saldo_bonus'] .'">
            </div>
            <div class="popupFooter">
                <button id="cancelBtn">Cancelar</button>
                <button id="saveBtnGreen">Salvar</button>
            </div>
        </div>';
    }
    echo($html);
} else {
    die();
}
?>