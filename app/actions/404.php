<?php

http_response_code(404);
header("Content-Type: application/json");
echo '{"error": "404 / Not Found"}';
