<?php

include_once 'php/connection.php';
include_once 'php/functions.php';




?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <title>Login - ADM</title>
</head>

<body>
    <main style="height: 100vh;">
        <div class="sinuca sinuca1">
            <img src="assets/images/amarelo.png" alt="">
        </div>
        <div class="sinuca sinuca2">
            <img src="assets/images/rosa-min.png" alt="">
        </div>
        <div class="sinuca sinuca3">
            <img src="assets/images/vermelho.png" alt="">
        </div>
        <div class="login__container">
            <div class="logo">
                <img src="assets/images/logo.png" alt="">
            </div>
            <div class="login">
                <h1>Entrar</h1>
                <form id="loginForm">
                    <label htmlFor="">E-mail</label>
                    <input type="text" id="email" placeholder="seuemail@gmail.com" />
                    <label htmlFor="">Senha</label>
                    <input type="password" id="senha" placeholder="Sua senha" />
                    <button type="submit" id="loginBtn">Entrar</button>
                </form>
            </div>
        </div>
    </main>
    <footer>
    </footer>
    <script src="js/login.js"></script>
</body>

</html>