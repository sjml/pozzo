<?php

// modded from Laravel's server.php file

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));


if (substr($uri, 0, 4) == "/api") {
    require_once __DIR__ . "/public/api/index.php";
    return true;
}

if (file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}
if ($uri[-1] == "/" && file_exists(__DIR__ . "/public" . $uri . "index.html")) {
    return false;
}

http_response_code(404);
echo '<pre>404 / Not Found</pre>';


