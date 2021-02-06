<?php

require_once __DIR__ . "/../lib/php-jwt-5.2.0/src/JWT.php";
require_once __DIR__ . "/../lib/php-jwt-5.2.0/src/ExpiredException.php";
require_once __DIR__ .
    "/../lib/php-jwt-5.2.0/src/SignatureInvalidException.php";
require_once __DIR__ . "/../lib/php-jwt-5.2.0/src/BeforeValidException.php";
use Firebase\JWT\JWT;

class Auth {
    private static function _getJWT() {
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            return -1;
        }
        $authHeader = $_SERVER["HTTP_AUTHORIZATION"];
        if (substr($authHeader, 0, strlen("Bearer ")) != "Bearer ") {
            return -2;
        }
        $token = substr($authHeader, strlen("Bearer "));
        return $token;
    }

    static function Validate() {
        $token = self::_getJWT();
        if ($token == -1) {
            return -1;
        }
        if ($token == -2) {
            return -2;
        }

        $secret = DB::GetConfig("app_key");

        $value = self::DecodeJWT($token, $secret);
        if (is_numeric($value)) {
            if ($value == -1) {
                return -3;
            }
            elseif ($value == -2) {
                return -4;
            }
            elseif ($value == -3) {
                return -5;
            }
            else {
                return $value;
            }
        }

        return 1;
    }

    static function GenerateKey() {
        return "base64:" . base64_encode(random_bytes(32));
    }

    static function GenerateJWT($userData, $secret, $validOffset, $expiration) {
        $issuer =
            "Pozzo / " .
            (isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "PHP-CLI");
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

        $jwt = JWT::encode($token, $secret, "HS256");
        return $jwt;
    }

    static function DecodeJWT($token, $secret) {
        try {
            $decoded = JWT::decode($token, $secret, ["HS256"]);
            return $decoded;
        } catch (\Firebase\JWT\ExpiredException $th) {
            return -1;
        } catch (\Firebase\JWT\BeforeValidException $th) {
            return -2;
        } catch (\Firebase\JWT\SignatureInvalidException $th) {
            return -3;
        } catch (\Throwable $th) {
            return -128;
        }
    }

    // just using the PHP default stuff, but wrapping in case
    //    it needs to be swapped out later

    static function HashPassword($rawPW) {
        return password_hash($rawPW, PASSWORD_DEFAULT);
    }

    static function VerifyPassword($rawPW, $hashedPW) {
        return password_verify($rawPW, $hashedPW);
    }
}
