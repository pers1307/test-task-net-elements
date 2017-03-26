<?php

$filepath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($filepath == '/') {

    include_once 'frontend/web/index.php';

    return true;
} elseif (substr_count($filepath, '/api/') == 1) {

    include_once 'api/web/index.php';

    return true;
} else {

    /**
     * Редирект уже осуществлен
     */
    if (substr_count($filepath, '/frontend/web/') != 0) {

        return false;
    }

    $file = __DIR__ . '/frontend/web/' . trim($filepath, '/');

    if (file_exists($file)) {

        $refer = $_SERVER['QUERY_STRING'];

        if ($refer != '') $refer = '?'.$refer;

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: /frontend/web' . $filepath . $refer);

        exit();
    }
}