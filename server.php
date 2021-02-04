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

if (isset($_ENV["ROLLUP_WATCH"])) {
    // running frontend dev; check there first
    if (doesFileExist($req_uri, "/frontend/public")) {
        $fp = getFilePath($req_uri, "/frontend/public");

        // efforts to use PHP's automagic mimetype detection stuff were too frustrating,
        //  and there's only a few kinds oif things that might get served here
        $ext = pathinfo($fp, PATHINFO_EXTENSION);
        switch ($ext) {
            case "html":
                header("Content-Type: text/html");
                break;
            case "css":
                header("Content-Type: text/css");
                break;
            case "js":
                header("Content-Type: text/javascript");
                break;
            case "map":
                header("Content-Type: application/json");
                break;
            default:
                throw new Exception("Unfamiliar file type in dev directory", 1);
                break;
        }
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

if (isset($_ENV["ROLLUP_WATCH"])) {
    // let the frontend dev server handle 404s if it's around
    require_once __DIR__ . "/public/index.html";
    return true;
}

http_response_code(404);
echo "<pre>404 / Not Found</pre>";
