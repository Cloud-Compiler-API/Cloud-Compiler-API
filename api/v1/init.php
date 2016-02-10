<?php
    require_once __DIR__ . '/../../include/php/autoload.php';

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
        $_REQUEST = stripslashes_deep($_REQUEST);
    }

    /*
     * Code snippet to accept json input too.
     * All the input parameters either GET or POST or JSON will be stored in $PARAMETERS.
     */
    $PARAMETERS = array(); //use this variable for all input parameters
    $raw_input = file_get_contents("php://input");
    // If there is json file input include all those params in $PARAMETERS
    if(isset($_SERVER['CONTENT_TYPE']) && (strcmp($_SERVER['CONTENT_TYPE'], "application/json") == 0)) {
        $input_params = json_decode($raw_input);
        if($input_params) {
            foreach($input_params as $param_name => $param_value) {
                $PARAMETERS[$param_name] = $param_value;
            }
        }
    }
    // Copy all the $_REQUEST variables into $PARAMETERS
    foreach($_REQUEST as $param_name => $param_value) {
        $PARAMETERS[$param_name] = $param_value;
    }

    /*
     * Including all files of API namespace
     */
    require_once __DIR__ . '/languages/Response.php';
    require_once __DIR__ . '/languages/template/Response.php';
    require_once __DIR__ . '/languages/sample/Response.php';
    require_once __DIR__ . '/maintain/Response.php';
    require_once __DIR__ . '/submissions/Response.php';