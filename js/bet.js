$(document).ready(function() {
    $.ajax({
        type: "GET",
        url: "php/get-all-bets.php",
        success: function(data) {
            if(data.includes("code")) {
                $("#betTableBody").html("<tr style='text-align: center'><td colspan='8'>Não há apostas disponíveis.</td></tr>");
            } else {
                $("#betTableBody").html(data);
            }
        },
        error: function(err) {
            console.warn(err);
        }
    });
});