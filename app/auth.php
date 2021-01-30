<?php

require_once __DIR__ . '/../lib/php-jwt-5.2.0/src/JWT.php';
require_once __DIR__ . '/../lib/php-jwt-5.2.0/src/ExpiredException.php';
require_once __DIR__ . '/../lib/php-jwt-5.2.0/src/SignatureInvalidException.php';
require_once __DIR__ . '/../lib/php-jwt-5.2.0/src/BeforeValidException.php';
use \Firebase\JWT\JWT;

function generateKey() {
    return 'base64:'.base64_encode(random_bytes(32));
}

function generateJWT($userData, $secret, $validOffset, $expiration) {
    $issuer = "Pozzo / " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "PHP-CLI");
    $issued_at = time();
    $notbefore = $issued_at + $validOffset;
    $expire = $issued_at + $expiration;

    $token = [
        "iss" => $issuer,
        "iat" => $issued_at,
        "nbf" => $notbefore,
        "exp" => $expire,
        "data" => $userData,
    ];

    $jwt = JWT::encode($token, $secret, 'HS256');
    return $jwt;
}

function validateJWT($token, $secret) {
    try {
        $decoded = JWT::decode($token, $secret, ['HS256']);
        return true;
    } catch (\Throwable $th) {
        return false;
    }
}

// just using the PHP default stuff, but wrapping in case
//    it needs to be swapped out later

function hashPassword($rawPW) {
    return password_hash($rawPW, PASSWORD_DEFAULT);
}

function verifyPassword($rawPW, $hashedPW) {
    return password_verify($rawPW, $hashedPW);
}
