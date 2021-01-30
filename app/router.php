<?php

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
                    // check and bounce 403 if needed
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
}
