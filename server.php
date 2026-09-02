<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/*
|--------------------------------------------------------------------------
| Serve Laravel storage files
|--------------------------------------------------------------------------
*/

if (str_starts_with($uri, '/storage/')) {

    $relativePath = substr($uri, strlen('/storage/'));

    $file = __DIR__ . '/storage/app/public/' . $relativePath;

    if (is_file($file)) {
        $mime = mime_content_type($file);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));

        readfile($file);

        return true;
    }

    http_response_code(404);

    return true;
}

/*
|--------------------------------------------------------------------------
| Serve normal public assets
|--------------------------------------------------------------------------
*/

$file = __DIR__ . '/public' . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

/*
|--------------------------------------------------------------------------
| Everything else → Laravel
|--------------------------------------------------------------------------
*/

require __DIR__ . '/public/index.php';