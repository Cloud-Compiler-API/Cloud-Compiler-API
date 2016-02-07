<?php
    /*
     * Including global variables
     */
    $config = include(dirname(__FILE__) . '/config.inc');
    $scrapeInfo = include(dirname(__FILE__) . '/scrapeInfo.inc');
    $langExtData = include(dirname(__FILE__) . '/langExtData.inc');

    spl_autoload_register(function($className) {
        if (strpos($className, 'Klein\\') === 0) {
            $dir = '/' . str_replace('\\', '/', $className);
            require_once __DIR__ . $dir . '.php';
        } else {
            $dir = '/' . $className . '.class';
            require_once __DIR__ . $dir . '.inc';
        }
    });