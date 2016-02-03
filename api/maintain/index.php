<?php
    require_once dirname(__FILE__) . '/../../include/php/Ideone.class.inc';

    /*
     * Utility function which strips escaped slashes deeply
     */
    function stripslashes_deep($value)  {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }

    $langs = Ideone::getLanguages();
    if(is_array($langs)) {
        $response['langUpdate'] = false;
        foreach($langs as $lang) {
            if($lang['fileExt'] == '') {
                $response['langUpdate'] = true;
                break;
            }
        }
    } else {
        $errorCode = $langs;
        if($errorCode == Ideone::CURL_ERROR || $errorCode == Ideone::SCRAPE_ERROR || $errorCode == Ideone::LOGIN_ERROR || $errorCode == Ideone::REDIRECTION_ERROR) {
            $error = 'SYSTEM_ERROR';
            $errorDesc = 'Some system error occurred, Please try again.';
        } else {
            $error = 'UNKNOWN_ERROR';
            $errorCode = Ideone::UNKNOWN_ERROR;
            $errorDesc = 'Some unknown error occurred, Please try again.';
        }
        $response['langUpdate'] = array(
            'error' => $error,
            'errorCode' => $errorCode,
            'errorDesc' => $errorDesc
        );
    }

    if(isset($_GET['cleanLog']) && $_GET['cleanLog'] == 1) {
        Ideone::cleanErrorLog();
    }

    if(isset($_GET['cleanAll']) && $_GET['cleanAll'] == 1) {
        Ideone::cleanAll();
    }

    $response['errorLog'] = Ideone::getErrorLog();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();