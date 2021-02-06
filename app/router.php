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

    function Output($obj, $code = 200) {
        http_response_code($code);
        header("Content-Type: application/json");
        echo json_encode($obj);
    }

    function Route() {
        if ("" == $_REQUEST["POZZO_REQUEST"]) {
            $_REQUEST["POZZO_REQUEST"] = "/";
        }
        foreach ($this->handlers as $prefix => $handler) {
            $prefixLength = strlen($prefix);
            $match = false;
            if (substr($prefix, -1) == "$") {
                $prefix = substr($prefix, 0, strlen($prefix) - 1);
                $prefixLength -= 1;
                $match = $prefix == $_REQUEST["POZZO_REQUEST"];
            } else {
                if (
                    substr($_REQUEST["POZZO_REQUEST"], 0, $prefixLength) ==
                    $prefix
                ) {
                    $match = true;
                }
            }

            if ($match) {
                if ($handler["requireLogin"]) {
                    if ($_REQUEST["POZZO_AUTH"] != 1) {
                        $status = 403;
                        $errData = [
                            "code" => $_REQUEST["POZZO_AUTH"],
                            "message" => "403 / Forbidden",
                        ];
                        switch ($_REQUEST["POZZO_AUTH"]) {
                            case 0:
                                $status = 500;
                                $errData["reason"] = "validation not performed";
                                break;
                            case -1:
                                $status = 400;
                                $errData["reason"] = "missing auth headers";
                                break;
                            case -2:
                                $status = 400;
                                $errData["reason"] = "missing bearer token";
                                break;
                            case -3:
                                $errData["reason"] = "expired";
                                break;
                            case -4:
                                $errData["reason"] = "beforeValid";
                                break;
                            case -5:
                                $errData["reason"] = "signatureInvalid";
                                break;
                            default:
                                $errData["reason"] = "unknown";
                                break;
                        }
                        self::Output($errData, $status);
                        return;
                    }
                }

                $_REQUEST["POZZO_REQUEST"] = substr(
                    $_REQUEST["POZZO_REQUEST"],
                    $prefixLength,
                );
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
}
