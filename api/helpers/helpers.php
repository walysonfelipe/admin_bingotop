<?php

function getPost(string $key): ?string
{
    return $_POST[$key] ?? null;
}

function cleanCpf(string $cpf): string
{
    return preg_replace('/[^A-Za-z0-9]/', '', $cpf);
}

function cleanPhone(string $phone): string
{
    $num = preg_replace('/[^0-9]/', '', $phone);
    $num = ltrim($num, '0');
    if (strlen($num) > 10) {
        $num = '55' . substr($num, -11);
    } else {
        $num = '55' . $num;
    }
    return $num;
}
