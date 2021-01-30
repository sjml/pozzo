<?php

// modded from Laravel's server.php file

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

if ($uri !== "/" && file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}

if (substr($uri, 0, 4) == "/api") {
    require_once __DIR__ . "/public/api/index.php";
    return true;
}

return false;
