$(document).ready(function () {
    updateWithdrawsTable();
});

$('body').on('click', '.process_withdraw', function () {
    if ($(this).data('type') == "accept") {
        if (confirm('Tem certeza que deseja liberar esse saque?') == true) {
            $(this).prop('disabled', true);
            $.ajax({
                type: "POST",
                url: "../api/createWithdrawApproval.php",
                xhrFields: {
                    withCredentials: true
                },
                data: {
                    withdrawId: $(this).data('withdrawid'),
                    type: $(this).data('type')
                },
                success: function (data) {
                    alert("Liberado com sucesso!");
                },
                error: function (err) {
                    console.warn(err);
                }
            });
        }
    } else {
        if (confirm('Tem certeza que deseja bloquear esse saque?') == true) {
            $(this).prop('disabled', true);
            $.ajax({
                type: "POST",
                url: "../api/createWithdrawApproval.php",
                data: {
                    withdrawId: $(this).data('withdrawid'),
                    type: $(this).data('type')
                },
                success: function (data) {
                    updateWithdrawsTable();
                    alert("Saque bloqueado.");
                },
                error: function (err) {
                    $(this).prop('disabled', false);
                    alert("Erro! Verificar console para detalhes.");
                    console.warn(err);
                }
            });
        }
    }
});

function updateWithdrawsTable() {
    $.ajax({
        type: "GET",
        url: "php/get-all-open-withdraws.php",
        success: function (data) {
            if (data.includes("code")) {
                $("#withdrawTableBody").html("<tr style='text-align: center'><td colspan='8'>Não há saques em espera</td></tr>");
            } else {
                $("#withdrawTableBody").html(data);
            }
        },
        error: function (err) {
            console.warn(err);
        }
    });
}