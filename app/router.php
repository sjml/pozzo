<?php

class Router {
    private $handlers = [];

    function AddHandler($prefix, $handler) {
        $this->handlers[$prefix] = $handler;
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
                $POZZO_REQUEST = substr($POZZO_REQUEST, $prefixLength);
                if ($handler[0] == "require") {
                    require $handler[1];
                } else {
                    call_user_func($handler[0], array_slice($handler, 1));
                }
                return;
            }
        }

        require __DIR__ . "/endpoints/404.php";
    }
}
