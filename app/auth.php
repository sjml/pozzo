<?php

use Firebase\JWT\JWT;

class Auth {
    static function GetJWT() {
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
        $token = self::GetJWT();
        if ($token == -1) {
            return -1;
        }
        if ($token == -2) {
            return -2;
        }

        $secret = DB::GetConfig("app_key");

        // @codeCoverageIgnoreStart
        // Not testing generation/decoding of JWTs
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
            elseif ($value == -4) {
                return -6;
            }
            else {
                return $value;
            }
        }
        // @codeCoverageIgnoreEnd

        return $value->data->id;
    }

    static function GenerateKey() {
        return "base64:" . base64_encode(random_bytes(32));
    }

    static function GenerateJWT($userData, $secret, $validOffset, $expiration) {
        $issuer =
            "Pozzo / " .
            (isset($_SERVER["SERVER_NAME"])
                ? $_SERVER["SERVER_NAME"]
                : "PHP-CLI");
        $issued_at = time();
        $notbefore = $issued_at + $validOffset;
        $expire = $issued_at + $expiration;

        $token = [
            "iss" => $issuer,
            "iat" => $issued_at,
            // "nbf" => $notbefore, // causing more problems than it's worth
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
        }
        // @codeCoverageIgnoreStart
        // Not testing generation/decoding of JWTs
        catch (\Firebase\JWT\ExpiredException $th) {
            return -1;
        }
        catch (\Firebase\JWT\BeforeValidException $th) {
            return -2;
        }
        catch (\Firebase\JWT\SignatureInvalidException $th) {
            return -3;
        }
        // @codeCoverageIgnoreEnd
        catch (\UnexpectedValueException $th) {
            return -4;
        }
        // @codeCoverageIgnoreStart
        // Dev only
        catch (\Throwable $th) {
            return -128;
        }
        // @codeCoverageIgnoreEnd
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
