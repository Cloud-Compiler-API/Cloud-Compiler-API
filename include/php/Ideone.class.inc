<?php
    require_once dirname(__FILE__) . '/curl.inc';
    $config = include(dirname(__FILE__) . '/config.inc');
    $scrapeInfo = include(dirname(__FILE__) . '/scrapeInfo.inc');

    /*
     * Utility function which is used for custom comparison while sorting
     */
    function customcmp($arr1, $arr2) {
        return strnatcasecmp($arr1['name'], $arr2['name']);
    }

    /*
     * Ideone API Class
     */
    class Ideone {
        private static $mainDOM;        // Contains DOM object of the main page
        private static $expiryTime;     // Contains the expiry time of mainDOM
        private static $sessionAlive;   // Tells whether mainDOM's session is alive or not

        //Unkown Error
        const UNKNOWN_ERROR = 400;
        //System Errors
        const CURL_ERROR = 500;
        const SCRAPE_ERROR = 501;
        const LOGIN_ERROR = 502;
        const REDIRECTION_ERROR = 503;
        //Input Errors
        const INVALID_TIME_INPUT = 600;
        const INVALID_LANG_ID = 601;
        const INVALID_SUBM_ID = 602;
        const SOURCECODE_UNDEFINED = 700;
        const LANGID_UNDEFINED = 701;

        /*
         * Function to reset the cache data stored in temp folder
         */
        private static function resetData() {
            Ideone::$mainDOM = null;
            Ideone::$expiryTime = null;
            Ideone::$sessionAlive = null;
            file_put_contents($GLOBALS['config']['tempLocation'] . '/main.html', '');
            file_put_contents($GLOBALS['config']['tempLocation'] . '/expiry.txt', '');
            file_put_contents($GLOBALS['config']['tempLocation'] . '/sessionAlive.txt', '');
            file_put_contents($GLOBALS['config']['tempLocation'] . '/cookie.txt', '');
        }

        /*
         * Creates a new session with an expiry as in config file
         */
        private static function newSession() {
            $response = curl($GLOBALS['scrapeInfo']['mainLink'], null, true);
            if($response['error'] == null) {
                Ideone::$mainDOM = new DOMDocument;
                @Ideone::$mainDOM->loadHTML($response['html']);
                Ideone::$expiryTime = time() + ($GLOBALS['config']['expiryTime']*86400);
                Ideone::$sessionAlive = true;

                // Writing files to use it as cache
                file_put_contents($GLOBALS['config']['tempLocation'] . '/main.html', $response['html']);
                file_put_contents($GLOBALS['config']['tempLocation'] . '/expiry.txt', Ideone::$expiryTime);
                file_put_contents($GLOBALS['config']['tempLocation'] . '/sessionAlive.txt', 'true');

                return true;
            } else {
                return Ideone::CURL_ERROR;
            }
        }

        /*
         * Loads the session from cache if it is not expired
         */
        private static function loadSession() {
            if(($expiryTime = file_get_contents($GLOBALS['config']['tempLocation'] . '/expiry.txt')) != '') {
                $currentTime = time();
                if($currentTime < $expiryTime) {    //Checking if the session expired or not
                    if(($mainHTML = file_get_contents($GLOBALS['config']['tempLocation'] . '/main.html')) != '') {
                        Ideone::$mainDOM = new DOMDocument;
                        @Ideone::$mainDOM->loadHTML($mainHTML);
                        Ideone::$expiryTime = $expiryTime;
                        if(($sessionAlive = file_get_contents($GLOBALS['config']['tempLocation'] . '/sessionAlive.txt')) == 'true') {
                            Ideone::$sessionAlive = true;
                        } else {
                            Ideone::$sessionAlive = false;
                        }

                        return true;
                    } else {
                        return Ideone::newSession();
                    }
                } else {
                    return Ideone::newSession();
                }
            } else {
                return Ideone::newSession();
            }
        }

        /*
         * Cleanly starts new session.
         */
        public static function refreshSession() {
            Ideone::resetData();
            return Ideone::newSession();
        }

        /*
         * Check whether the session is logged in one or not
         */
        private static function checkLogin() {
            if(($result = Ideone::loadSession()) === true) {
                if(($nav = Ideone::$mainDOM->getElementById($GLOBALS['scrapeInfo']['loginNavID'])) != null) {
                    if(strpos($nav->nodeValue, $GLOBALS['scrapeInfo']['loginSearch'])) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return Ideone::SCRAPE_ERROR;
                }
            } else {
                return $result;
            }
        }

        /*
         * Login and create new session if not logged in
         */
        private static function login() {
            $result = Ideone::checkLogin();
            if($result == false) {
                $data = array(
                    'username' => $GLOBALS['config']['username'],
                    'password' => $GLOBALS['config']['password'],
                    'remember' => 'yes',
                    'next' => ''
                );
                $response = curl($GLOBALS['scrapeInfo']['loginLink'], $data, true);
                if($response['error'] == null) {
                    if($response['response']['http_code'] == 302) {
                        return Ideone::newSession();
                    } else {
                        return Ideone::LOGIN_ERROR;
                    }
                } else {
                    return Ideone::CURL_ERROR;
                }
            } else {
                return $result;
            }
        }

        /*
         * Returns an associative array containing details about languages
         */
        public static function getLanguages() {
            if(($result = Ideone::loadSession()) === true) {
                if(($menu = Ideone::$mainDOM->getElementById($GLOBALS['scrapeInfo']['langDropDownMenuID'])) != null) {
                    $lists = $menu->getElementsByTagName('ul');
                    $languages = array();
                    $flag = 0;
                    foreach($lists as $list) {
                        if($list->getAttribute('class') == $GLOBALS['scrapeInfo']['langPopularClass'] || $list->getAttribute('class') == $GLOBALS['scrapeInfo']['langRestClass']) {
                            $items = $list->getElementsByTagName('a');
                            foreach($items as $item) {
                                if(($item->getAttribute($GLOBALS['scrapeInfo']['langIdAttr']) != null) && ($item->getAttribute($GLOBALS['scrapeInfo']['langLabelAttr']) != null) && ($item->getAttribute($GLOBALS['scrapeInfo']['langTitleAttr']) != null)) {
                                    $id = trim($item->getAttribute($GLOBALS['scrapeInfo']['langIdAttr']));
                                    $name = trim($item->getAttribute($GLOBALS['scrapeInfo']['langLabelAttr']));
                                    $version = substr(substr(trim($item->getAttribute($GLOBALS['scrapeInfo']['langTitleAttr'])), (strlen($name) + 2)), 0, -1);
                                    $popular = (($list->getAttribute('class') == $GLOBALS['scrapeInfo']['langPopularClass']) ? true : false);
                                    $lang_desc = array(
                                        'id' => $id,
                                        'name' => $name,
                                        'version' => $version,
                                        'popular' => $popular
                                    );
                                    array_push($languages, $lang_desc);
                                    $flag = 1;
                                } else {
                                    return Ideone::SCRAPE_ERROR;
                                }
                            }
                        }
                    }
                    if($flag) {
                        usort($languages, 'customcmp');
                        return $languages;
                    } else {
                        return Ideone::SCRAPE_ERROR;
                    }
                } else {
                    return Ideone::SCRAPE_ERROR;
                }
            } else {
                return $result;
            }
        }

        /*
         * Updates the given id with original language ID of Ideone
         */
        private static function getOrigLangID(&$id) {
            $result = Ideone::getLanguages();
            if(is_array($result)) {
                $minID = 0;
                $maxID = count($result) - 1;
                if(($id >= $minID) && ($id <= $maxID)) {
                    $id = $result[$id]['id'];
                    return true;
                } else {
                    return Ideone::INVALID_LANG_ID;
                }
            } else {
                return $result;
            }
        }

        /*
         * Gives language ID with the input of language name and version
         */
        private static function getLangID($langName, $langVersion) {
            $langs = Ideone::getLanguages();
            if(is_array($langs)) {
                $n = count($langs);
                for ($i = 0; $i < $n; $i++) {
                    if($langName == $langs[$i]['name'] && $langVersion == $langs[$i]['version']) {
                        return $i;
                    }
                }
                return Ideone::SCRAPE_ERROR;
            } else {
                return $langs;
            }
        }

        /*
         * Returns ID after submission of source code
         */
        public static function getID($code, $lang, $stdin = '', $time = 0) {
            $origLang = $lang;
            if(!($time == 0 || $time == 1)) {
                return Ideone::INVALID_TIME_INPUT;
            }
            if(($check = Ideone::getOrigLangID($origLang)) !== true) {
                return $check;
            }
            if(($result = Ideone::loadSession()) === true) {
                if(Ideone::$sessionAlive) {
                    if(($p1 = Ideone::$mainDOM->getElementById($GLOBALS['scrapeInfo']['sessionID'])) != null) {
                        if($time == 1) {
                            if(($result = Ideone::login()) !== true) {
                                return $result;
                            }
                        }
                        $data = array(
                            'p1' => $p1->getAttribute('value'),
                            'p2' => '',
                            'p3' => '',
                            'p4' => '',
                            'clone_link' => '/',
                            'file' => $code,
                            'input' => $stdin,
                            'syntax' => 1,
                            'timelimit' => $time,
                            'note' => '',
                            '_lang' => $origLang,
                            'public' => 0,
                            'run' => 1,
                            'Submit' => ''
                        );
                        $response = curl($GLOBALS['scrapeInfo']['submissionLink'], $data, true, true, true);
                        if($response['error'] == null) {
                            // Generating redirect_url for older curl versions
                            if(!isset($response['response']['redirect_url']) && $response['response']['http_code'] == 302) {
                                preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $response['html'], $matches);
                                $redirect_url = trim($matches[1]);
                                if($redirect_url != '') {
                                    $response['response']['redirect_url'] = $redirect_url;
                                } else {
                                    return Ideone::REDIRECTION_ERROR;
                                }
                            }
                            if($response['response']['redirect_url'] == $GLOBALS['scrapeInfo']['mainLink']) {
                                if(($result2 = Ideone::newSession()) === true) {
                                    return Ideone::getID($code, $lang, $stdin, $time);
                                } else {
                                    return $result2;
                                }
                            } else {
                                $parsed_url = parse_url($response['response']['redirect_url']);
                                $id = substr($parsed_url['path'], 1);
                                Ideone::$sessionAlive = false;
                                file_put_contents($GLOBALS['config']['tempLocation'] . '/sessionAlive.txt', '');

                                return $id;
                            }
                        } else {
                            return Ideone::CURL_ERROR;
                        }
                    } else {
                        return Ideone::SCRAPE_ERROR;
                    }
                } else {
                    if(($result3 = Ideone::newSession()) === true) {
                        return Ideone::getID($code, $lang, $stdin, $time);
                    } else {
                        return $result3;
                    }
                }
            } else {
                return $result;
            }
        }

        /*
         * Returns an associative array containing output details of the ID provided
         */
        public static function getOutput($id) {
            $url = $GLOBALS['scrapeInfo']['ajaxSubLink1'] . $id . $GLOBALS['scrapeInfo']['ajaxSubLink2'];
            $response = curl($url);
            if($response['error'] == null) {
                if($response['html'] == null) {
                    return Ideone::INVALID_SUBM_ID;
                } else {
                    $obj = json_decode($response['html']);
                    $output = array(
                        'status' => $obj->status,
                        'result' => $obj->result,
                        'any_cmperr' => ($obj->any_cmperr ? true : false),
                        'stdout' => $obj->stdout,
                        'stderr' => $obj->stderr,
                        'cmperr' => $obj->cmperr,
                        'time' => $obj->time,
                        'memory' => $obj->memory,
                        'signal' => $obj->signal
                    );
                    return $output;
                }
            } else {
                return Ideone::CURL_ERROR;
            }
        }

        /*
         * Returns the source code with the input of id
         */
        public static function getSourceCode($id) {
            $url = $GLOBALS['scrapeInfo']['sourceCodeLink'] . $id;
            $response = curl($url);
            if($response['error'] == null) {
                if($response['html'] != '') {
                    return $response['html'];
                } else {
                    return Ideone::INVALID_SUBM_ID;
                }
            } else {
                return Ideone::CURL_ERROR;
            }
        }

        /*
         * Returns associative array containing input data with the input of id
         */
        public static function getInputData($id) {
            $url = $GLOBALS['scrapeInfo']['mainLink'] . $id;
            $response = curl($url);
            if($response['error'] == null) {
                if($response['response']['http_code'] == 302) {
                    return Ideone::INVALID_SUBM_ID;
                } else {
                    $inputData = array();
                    $tempDOM = new DOMDocument;
                    @$tempDOM->loadHTML($response['html']);

                    if(($stdinTag = $tempDOM->getElementById($GLOBALS['scrapeInfo']['stdinID'])) != null) {
                        $stdinData = $stdinTag->getElementsByTagName('pre');
                        if($stdinData->item(0)->nodeValue == $GLOBALS['scrapeInfo']['stdinEmpty']) {
                            $inputData['stdin'] = '';
                        } else {
                            $inputData['stdin'] = $stdinData->item(0)->nodeValue;
                        }
                    } else {
                        return Ideone::SCRAPE_ERROR;
                    }

                    if(($infoTag = $tempDOM->getElementById($GLOBALS['scrapeInfo']['infoID'])) != null) {
                        $langTag = $infoTag->getElementsByTagName('strong');
                        if($langTag->length == $GLOBALS['scrapeInfo']['strongTagLength']) {
                            $inputData['langName'] = $langTag->item(0)->nodeValue;
                            $inputData['langVersion'] = $langTag->item(0)->attributes->item(1)->nodeValue;
                            $inputData['langId'] = Ideone::getLangID($inputData['langName'], $inputData['langVersion']);
                        } else {
                            return Ideone::SCRAPE_ERROR;
                        }
                    }

                    if(($dateTag = $tempDOM->getElementById($GLOBALS['scrapeInfo']['dateID'])) != null) {
                        $dateSpan = $dateTag->getElementsByTagName('span');
                        $date = substr($dateSpan->item(0)->attributes->item(1)->nodeValue, 0, -4);
                        $dateObj = new DateTime($date, new DateTimeZone('CET'));
                        $inputData['timestamp'] = $dateObj->format('U');
                    } else {
                        return Ideone::SCRAPE_ERROR;
                    }

                    return $inputData;
                }
            } else {
                return Ideone::CURL_ERROR;
            }
        }
    }