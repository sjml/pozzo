<?php

require_once __DIR__ . "/db.php";

class Router {
    private $handlers = [];

    function AddHandler($prefix, $handler, $requireLogin=false) {
        $this->handlers[$prefix] = ["call" => $handler, "requireLogin" => $requireLogin];
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
                $prefix = substr($prefix, 0, strlen($prefix)-1);
                $prefixLength -= 1;
                $match = ($prefix == $POZZO_REQUEST);
            }
            else {
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

    private function _validate() {
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            return false;
        }
        $authHeader = $_SERVER["HTTP_AUTHORIZATION"];
        if (substr($authHeader, 0, strlen("Bearer ")) != "Bearer ") {
            output(["message" => "Missing bearer token"], 400);
            return;
        }
        $token = substr($authHeader, strlen("Bearer "));

        //// from when it was POST-ed instead of in the headers
        // $input = json_decode(file_get_contents("php://input"), true);
        // if (!isset($input["token"])) {
        //     output(["message" => "Missing parameter 'token'"], 400);
        //     return;
        // }
        // $token = $input["token"];

        $secret = DB::GetConfig("app_key");

        return validateJWT($token, $secret);
    }
}
