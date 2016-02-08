<?php
    namespace API\Languages;

    require_once __DIR__ . '/../../../include/php/autoload.php';

    function Response() {
        $langs = \Ideone::getLanguages();
        if(is_array($langs)) {
            $n = count($langs);
            $response['body'] = array();
            $response['code'] = 200;

            for ($i = 0; $i < $n; $i++) {
                $temp = array(
                    'id' => $i,
                    'name' => $langs[$i]['name']
                );
                if(isset($_REQUEST['withVersion']) && $_REQUEST['withVersion'] == 1) {
                    $temp['version'] = $langs[$i]['version'];
                }
                if(isset($_REQUEST['withPopular']) && $_REQUEST['withPopular'] == 1) {
                    $temp['popular'] = $langs[$i]['popular'];
                }
                if(isset($_REQUEST['withFileExt']) && $_REQUEST['withFileExt'] == 1) {
                    $temp['fileExt'] = $langs[$i]['fileExt'];
                }
                array_push($response['body'], $temp);
            }

            if(isset($_REQUEST['onlyPopular']) && $_REQUEST['onlyPopular'] == 1 && !isset($_REQUEST['onlyUnpopular'])) {
                for ($i = 0; $i < $n; $i++) {
                    if(!$langs[$i]['popular']) {
                        unset($response['body'][$i]);
                    }
                }
                $response['body'] = array_values($response['body']);
            }
            if(isset($_REQUEST['onlyUnpopular']) && $_REQUEST['onlyUnpopular'] == 1 && !isset($_REQUEST['onlyPopular'])) {
                for ($i = 0; $i < $n; $i++) {
                    if($langs[$i]['popular']) {
                        unset($response['body'][$i]);
                    }
                }
                $response['body'] = array_values($response['body']);
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
            $response['body'] = array(
                'error' => $error,
                'errorCode' => $errorCode,
                'errorDesc' => $errorDesc
            );
        }

        return $response;
    }