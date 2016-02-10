<?php
    namespace API\Maintain;

    require_once __DIR__ . '/../../../include/php/autoload.php';

    function Response() {
        $langs = \Ideone::getLanguages();
        if(is_array($langs)) {
            $response['code'] = 200;
            $response['body']['langUpdate'] = false;
            foreach($langs as $lang) {
                if($lang['fileExt'] == '') {
                    $response['body']['langUpdate'] = true;
                    break;
                }
            }
        } else {
            $response['code'] = 500;
            $errorCode = $langs;
            if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                $error = 'SYSTEM_ERROR';
                $errorDesc = 'Some system error occurred, Please try again.';
            } else {
                $error = 'UNKNOWN_ERROR';
                $errorCode = \Ideone::UNKNOWN_ERROR;
                $errorDesc = 'Some unknown error occurred, Please try again.';
            }
            $response['body']['langUpdate'] = array(
                'error' => $error,
                'errorCode' => $errorCode,
                'errorDesc' => $errorDesc
            );
        }

        if(isset($GLOBALS['PARAMETERS']['cleanLog']) && $GLOBALS['PARAMETERS']['cleanLog'] == 1) {
            \Ideone::cleanErrorLog();
        }

        if(isset($GLOBALS['PARAMETERS']['cleanAll']) && $GLOBALS['PARAMETERS']['cleanAll'] == 1) {
            \Ideone::cleanAll();
        }

        $response['body']['errorLog'] = \Ideone::getErrorLog();

        return $response;
    }