$("#loginBtn").click(function() {
    e.preventDefault();
    $("#loginForm").submit();
});

$("#loginForm").submit(function(e) {
    e.preventDefault();
    let formData = {
        email: $("#email").val(),
        senha: $("#senha").val(),
    };
    $.ajax({
        type: 'POST',
        url: 'php/login.php',
        data: formData,
        success: function(data) {
            const response = typeof data === "string" ? JSON.parse(data) : data;
            if(response.success == false) {
                alert("Usuário ou senha incorretos!");
            } else {
                window.location.href = "resultados.php";
            }
        },
        error: function(err) {
            console.warn(err);
        }
    });
});

$("#showPassword").change(function() {
    if(this.checked) {
        $("#senha").attr("type", "text");
        $("#repeatSenha").attr("type", "text");
    } else {
        $("#senha").attr("type", "password");
        $("#repeatSenha").attr("type", "password");
    }
});