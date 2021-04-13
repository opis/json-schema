<?php

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $dir = __DIR__ . '/src';
    $namespace = 'Opis\JsonSchema';
    if (strpos($class, $namespace) === 0) {
        $class = substr($class, strlen($namespace));
        $path = '';
        if (($pos = strripos($class, '\\')) !== false) {
            $path = str_replace('\\', '/', substr($class, 0, $pos)) . '/';
            $class = substr($class, $pos + 1);
        }
        $path .= str_replace('_', '/', $class) . '.php';
        $dir .= '/' . $path;
        if (is_file($dir)) {
            include $dir;
            return true;
        }
        return false;
    }
    return false;
});