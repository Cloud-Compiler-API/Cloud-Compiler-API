<?php
    namespace API\Languages;

    require_once __DIR__ . '/../../include/php/autoload.php';

    function Response() {
        $langs = \Ideone::getLanguages();
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
            if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                $error = 'SYSTEM_ERROR';
                $errorDesc = 'Some system error occurred, Please try again.';
            } else {
                $error = 'UNKNOWN_ERROR';
                $errorCode = \Ideone::UNKNOWN_ERROR;
                $errorDesc = 'Some unknown error occurred, Please try again.';
            }
            $response = array(
                'error' => $error,
                'errorCode' => $errorCode,
                'errorDesc' => $errorDesc
            );
        }

        return $response;
    }