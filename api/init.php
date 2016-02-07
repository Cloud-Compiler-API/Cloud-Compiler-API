<?php
    require_once __DIR__ . '/../include/php/autoload.php';

    /*
     * Utility function which strips escaped slashes deeply
     */
    function stripslashes_deep($value)
    {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }

    // Stripping slashes to request variables if magic_quotes is set
    if((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!="off"))) {
        $_GET = stripslashes_deep($_GET);
        $_POST = stripslashes_deep($_POST);
        $_COOKIE = stripslashes_deep($_COOKIE);
    }

    /*
     * Including all files of API namespace
     */
    require_once __DIR__ . '/languages/Response.php';
    require_once __DIR__ . '/languages/template/Response.php';
    require_once __DIR__ . '/languages/sample/Response.php';
    require_once __DIR__ . '/maintain/Response.php';
    require_once __DIR__ . '/submissions/Response.php';