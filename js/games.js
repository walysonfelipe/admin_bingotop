$(document).ready(function() {
    $.ajax({
        type: "GET",
        url: "php/get-all-games.php",
        success: function(data) {
            if(data.includes("code")) {
                $("#gamesTableBody").html("<tr style='text-align: center'><td colspan='8'>Não há apostas disponíveis.</td></tr>");
            } else {
                $("#gamesTableBody").html(data);
            }
        },
        error: function(err) {
            console.warn(err);
        }
    });
});