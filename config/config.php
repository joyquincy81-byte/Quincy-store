<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';

if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

spl_autoload_register(static function (string $class): void {
    $path = ROOT_PATH . '/classes/' . $class . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});