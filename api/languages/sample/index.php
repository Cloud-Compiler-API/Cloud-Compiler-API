<?php
    require_once __DIR__ . '/../../../include/php/Ideone.class.inc';

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

    if(isset($_GET['langId'])) {
        $result = Ideone::getSample($_GET['langId']);
        if(is_array($result)) {
            $response = $result;
        } else {
            $errorCode = $result;
            if($errorCode == Ideone::CURL_ERROR || $errorCode == Ideone::SCRAPE_ERROR || $errorCode == Ideone::LOGIN_ERROR || $errorCode == Ideone::REDIRECTION_ERROR) {
                $error = 'SYSTEM_ERROR';
                $errorDesc = 'Some system error occurred, Please try again.';
            } else if($errorCode == Ideone::INVALID_LANG_ID) {
                $error = 'INVALID_LANG_ID';
                $errorDesc = 'Invalid input for langId.';
            } else if($errorCode == Ideone::NO_SAMPLE_AVAILABLE) {
                $error = 'NO_SAMPLE_AVAILABLE';
                $errorDesc = 'No sample code available for the given language.';
            } else {
                $error = $result;
                $errorCode = Ideone::UNKNOWN_ERROR;
                $errorDesc = 'Some unknown error occurred, Please try again.';
            }
            $response = array(
                'error' => $error,
                'errorCode' => $errorCode,
                'errorDesc' => $errorDesc
            );
        }
    } else {
        $response = array(
            'error' => 'LANGID_UNDEFINED',
            'errorCode' => Ideone::LANGID_UNDEFINED,
            'errorDesc' => 'Language ID is not provided in the input.'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();