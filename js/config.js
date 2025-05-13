$("#configForm").submit(function(e) {
    e.preventDefault();
    if(confirm("Tem certeza que deseja alterar os valores?") == true) {
        let minDeposito = $("#minDeposito").val();
        let minEntrada = $("#minEntrada").val();
        let padraoPremio1 = $("#padraoPremio1").val();
        let padraoPremio2 = $("#padraoPremio2").val();
        let padraoPremio3 = $("#padraoPremio3").val();
        let mensagemPopup = $("#mensagemPopup").val();
        let tempoJogos = $("#tempoJogos").val();
        let bonusAtivo = $("#bonusAtivo").is(":checked");
        let tempoBonusInicio = $("#tempoBonusInicio").val();
        let tempoBonusFim = $("#tempoBonusFim").val();
        let promoCode = $("#promoCode").val();
        
        $.ajax({
            type: "POST",
            url: "php/update-configs.php",
            data: {
                minDeposito: minDeposito,
                minEntrada: minEntrada,
                padraoPremio1: padraoPremio1,
                padraoPremio2: padraoPremio2,
                padraoPremio3: padraoPremio3,
                mensagemPopup: mensagemPopup,
                tempoJogos: tempoJogos,
                bonusAtivo: bonusAtivo,
                tempoBonusFim: tempoBonusFim,
                tempoBonusInicio: tempoBonusInicio,
                promoCode: promoCode
            },
            success: function (data) {
                alert("Salvo com sucesso!");
            },
            error: function(err) {
                console.warn(err);
                alert("Erro! Verificar o console para mais informações.");
            }
        });
    }
})