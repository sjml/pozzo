<?php

require_once __DIR__ . "/db.php";

class Router {
    private $handlers = [];

    function AddHandler($prefix, $handler, $requireLogin = false) {
        $this->handlers[$prefix] = [
            "call" => $handler,
            "requireLogin" => $requireLogin,
        ];
    }

    function Route() {
        global $POZZO_REQUEST;
        if ("" == $POZZO_REQUEST) {
            $POZZO_REQUEST = "/";
        }
        foreach ($this->handlers as $prefix => $handler) {
            $prefixLength = strlen($prefix);
            $match = false;
            if (substr($prefix, -1) == "$") {
                $prefix = substr($prefix, 0, strlen($prefix) - 1);
                $prefixLength -= 1;
                $match = $prefix == $POZZO_REQUEST;
            } else {
                if (substr($POZZO_REQUEST, 0, $prefixLength) == $prefix) {
                    $match = true;
                }
            }

            if ($match) {
                if ($handler["requireLogin"]) {
                    if (!self::_validate()) {
                        http_response_code(403);
                        header("Content-Type: application/json");
                        echo json_encode(["message" => "403 / Forbidden"]);
                        return;
                    }
                }

                $POZZO_REQUEST = substr($POZZO_REQUEST, $prefixLength);
                $call = $handler["call"];
                if ($call[0] == "require") {
                    require $call[1];
                } else {
                    call_user_func($call[0], array_slice($call, 1));
                }
                return;
            }
        }

        require __DIR__ . "/endpoints/404.php";
    }

    function GetJWT() {
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

    private function _validate() {
        $token = self::GetJWT();
        if ($token == -1) {
            return false;
        }
        if ($token == -2) {
            output(["message" => "Missing bearer token"], 400);
            return;
        }

        $secret = DB::GetConfig("app_key");

        $value = decodeJWT($token, $secret);
        if ($value == false) {
            return false;
        }
        return true;
    }
}
