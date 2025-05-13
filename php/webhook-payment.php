<?php
include 'connection.php';

// Pega os dados da Webhook em JSON, transforma em array
$json = file_get_contents('php://input');

$webhook_data = json_decode($json, true);

$transaction_id = $webhook_data['transactionOrderId'];
if($webhook_data['transactionState'] == "Completed") {
    $update_transaction_sql = "UPDATE withdraws SET status = 3 WHERE withdraw_id = ?";
    $update_transaction_stmt = $mysqli->prepare($update_transaction_sql);
    $update_transaction_stmt->bind_param(
        "i", $transaction_id
    );
    $update_transaction_result = $update_transaction_stmt->execute();

    echo('{"message": "Funcionou corretamente!"}');
} else if($webhook_data['transactionState'] == "Cancelled" || $webhook_data['transactionState'] == "Error") {
    $update_transaction_sql = "UPDATE withdraws SET status = 2 WHERE withdraw_id = ?";
    $update_transaction_stmt = $mysqli->prepare($update_transaction_sql);
    $update_transaction_stmt->bind_param(
        "i", $transaction_id
    );
    $update_transaction_result = $update_transaction_stmt->execute();
}

?>