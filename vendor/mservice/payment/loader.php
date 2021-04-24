<?php
spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $fileName = __DIR__ . "/src/" . $className . ".php";
    if (file_exists($fileName)) {
        include_once $fileName;
    }
});
