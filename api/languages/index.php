<?php
    require_once dirname(__FILE__) . '/../../include/php/Ideone.class.inc';

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

    $langs = Ideone::getLanguages();
    if(is_array($langs)) {
        $n = count($langs);
        $response = array();

        for ($i = 0; $i < $n; $i++) {
            $temp = array(
                'id' => $i,
                'name' => $langs[$i]['name']
            );
            if(isset($_GET['withVersion']) && $_GET['withVersion'] == 1) {
                $temp['version'] = $langs[$i]['version'];
            }
            if(isset($_GET['withPopular']) && $_GET['withPopular'] == 1) {
                $temp['popular'] = $langs[$i]['popular'];
            }
            if(isset($_GET['withFileExt']) && $_GET['withFileExt'] == 1) {
                $temp['fileExt'] = $langs[$i]['fileExt'];
            }
            array_push($response, $temp);
        }

        if(isset($_GET['onlyPopular']) && $_GET['onlyPopular'] == 1 && !isset($_GET['onlyUnpopular'])) {
            for ($i = 0; $i < $n; $i++) {
                if(!$langs[$i]['popular']) {
                    unset($response[$i]);
                }
            }
            $response = array_values($response);
        }
        if(isset($_GET['onlyUnpopular']) && $_GET['onlyUnpopular'] == 1 && !isset($_GET['onlyPopular'])) {
            for ($i = 0; $i < $n; $i++) {
                if($langs[$i]['popular']) {
                    unset($response[$i]);
                }
            }
            $response = array_values($response);
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
        $response = array(
            'error' => $error,
            'errorCode' => $errorCode,
            'errorDesc' => $errorDesc
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();