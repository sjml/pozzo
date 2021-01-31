<?php

// inspired by Laravel's server.php file

$req_uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

function getFilePath($uri, $baseDir) {
    $base = __DIR__ . $baseDir . $uri;
    if ($uri[-1] == "/") {
        return $base . "index.html";
    }
    return $base;
}

function doesFileExist($uri, $baseDir) {
    $fp = getFilePath($uri, $baseDir);
    if (file_exists($fp)) {
        return true;
    }
    if ($uri[-1] == "/" && file_exists($fp . "index.html")) {
        return true;
    }
}

if (isset($_ENV['ROLLUP_WATCH'])) {
    // running frontend dev; check there first
    if (doesFileExist($req_uri, "/frontend/public")) {
        $fp = getFilePath($req_uri, "/frontend/public");

        header("Content-Type: " . mime_content_type($fp));
        readfile($fp);

        return true;
    }
}

if (doesFileExist($req_uri, "/public")) {
    return false; // means it'll get served as a static file
}

if (substr($req_uri, 0, 4) == "/api") {
    require_once __DIR__ . "/public/api/index.php";
    return true;
}



http_response_code(404);
echo "<pre>404 / Not Found</pre>";
