<?php
    namespace API\Maintain;

    require_once __DIR__ . '/../../include/php/autoload.php';

    function Response() {
        $langs = \Ideone::getLanguages();
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
            if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                $error = 'SYSTEM_ERROR';
                $errorDesc = 'Some system error occurred, Please try again.';
            } else {
                $error = 'UNKNOWN_ERROR';
                $errorCode = \Ideone::UNKNOWN_ERROR;
                $errorDesc = 'Some unknown error occurred, Please try again.';
            }
            $response['langUpdate'] = array(
                'error' => $error,
                'errorCode' => $errorCode,
                'errorDesc' => $errorDesc
            );
        }

        if(isset($_GET['cleanLog']) && $_GET['cleanLog'] == 1) {
            \Ideone::cleanErrorLog();
        }

        if(isset($_GET['cleanAll']) && $_GET['cleanAll'] == 1) {
            \Ideone::cleanAll();
        }

        $response['errorLog'] = \Ideone::getErrorLog();

        return $response;
    }