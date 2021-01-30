<?php

class Router {
    private $handlers = [];

    function AddHandler($prefix, $handler) {
        $this->handlers[$prefix] = $handler;
    }

    function Route() {
        global $POZZO_REQUEST;
        foreach ($this->handlers as $prefix => $handler) {
            $prefixLength = strlen($prefix);
            if (substr($POZZO_REQUEST, 0, $prefixLength) == $prefix) {
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
