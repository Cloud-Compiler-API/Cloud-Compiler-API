<?php
    namespace API\Submissions;

    require_once __DIR__ . '/../../../include/php/autoload.php';

    function Response($id = null) {
        if($id != null) {
            $output = \Ideone::getOutput($id);
            if(is_array($output)) {
                $response['code'] = 200;
                $response['body'] = $output;
                if(isset($GLOBALS['PARAMETERS']['withSource']) && $GLOBALS['PARAMETERS']['withSource'] == 1) {
                    $sourceCode = \Ideone::getSourceCode($id);
                    if(is_string($sourceCode)) {
                        $response['body']['sourceCode'] = $sourceCode;
                    } else {
                        $errorCode = $sourceCode;
                        if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                            $response['code'] = 500;
                            $error = 'SYSTEM_ERROR';
                            $errorDesc = 'Some system error occurred, Please try again.';
                        } else if($errorCode == \Ideone::INVALID_SUBM_ID) {
                            $response['code'] = 400;
                            $error = 'INVALID_SUBM_ID';
                            $errorDesc = 'Invalid input for the submission id.';
                        } else {
                            $response['code'] = 500;
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
                }
                if(isset($GLOBALS['PARAMETERS']['withInput']) || isset($GLOBALS['PARAMETERS']['withLang']) || isset($GLOBALS['PARAMETERS']['withTimestamp'])) {
                    $input = \Ideone::getInputData($id);
                    if(is_array($input)) {
                        if(isset($GLOBALS['PARAMETERS']['withInput']) && $GLOBALS['PARAMETERS']['withInput'] == 1) {
                            $response['body']['stdin'] = $input['stdin'];
                        }
                        if(isset($GLOBALS['PARAMETERS']['withLang']) && $GLOBALS['PARAMETERS']['withLang'] == 1) {
                            $response['body']['langId'] = $input['langId'];
                            $response['body']['langName'] = $input['langName'];
                            $response['body']['langVersion'] = $input['langVersion'];
                        }
                        if(isset($GLOBALS['PARAMETERS']['withTimestamp']) && $GLOBALS['PARAMETERS']['withTimestamp'] == 1) {
                            $response['body']['timestamp'] = $input['timestamp'];
                        }
                    } else {
                        $errorCode = $input;
                        if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                            $response['code'] = 500;
                            $error = 'SYSTEM_ERROR';
                            $errorDesc = 'Some system error occurred, Please try again.';
                        } else if($errorCode == \Ideone::INVALID_SUBM_ID) {
                            $response['code'] = 400;
                            $error = 'INVALID_SUBM_ID';
                            $errorDesc = 'Invalid input for the submission id.';
                        } else {
                            $response['code'] = 500;
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
                }
            } else {
                $errorCode = $output;
                if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                    $response['code'] = 500;
                    $error = 'SYSTEM_ERROR';
                    $errorDesc = 'Some system error occurred, Please try again.';
                } else if($errorCode == \Ideone::INVALID_SUBM_ID) {
                    $response['code'] = 400;
                    $error = 'INVALID_SUBM_ID';
                    $errorDesc = 'Invalid input for the submission id.';
                } else {
                    $response['code'] = 500;
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
        } else {
            if(isset($GLOBALS['PARAMETERS']['sourceCode']) && isset($GLOBALS['PARAMETERS']['langId'])) {
                $sourceCode = $GLOBALS['PARAMETERS']['sourceCode'];
                $langId = $GLOBALS['PARAMETERS']['langId'];
                if(isset($GLOBALS['PARAMETERS']['stdin'])) {
                    $stdin = $GLOBALS['PARAMETERS']['stdin'];
                } else {
                    $stdin = '';
                }
                if(isset($GLOBALS['PARAMETERS']['timeLimit'])) {
                    $time = $GLOBALS['PARAMETERS']['timeLimit'];
                } else {
                    $time = 0;
                }
                $result = \Ideone::getID($sourceCode, $langId, $stdin, $time);
                if(is_string($result)) {
                    $response['code'] = 200;
                    $response['body'] = array(
                        'id' => $result,
                    );
                } else {
                    $errorCode = $result;
                    if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR) {
                        $response['code'] = 500;
                        $error = 'SYSTEM_ERROR';
                        $errorDesc = 'Some system error occurred, Please try again.';
                    } else if($errorCode == \Ideone::INVALID_TIME_INPUT) {
                        $response['code'] = 400;
                        $error = 'INVALID_TIME_INPUT';
                        $errorDesc = 'Invalid input for timeLimit.';
                    } else if($errorCode == \Ideone::INVALID_LANG_ID) {
                        $response['code'] = 400;
                        $error = 'INVALID_LANG_ID';
                        $errorDesc = 'Invalid input for langId.';
                    } else {
                        $response['code'] = 500;
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
            } else {
                if(isset($GLOBALS['PARAMETERS']['sourceCode'])) {
                    $response['code'] = 400;
                    $response['body'] = array(
                        'error' => 'LANGID_UNDEFINED',
                        'errorCode' => \Ideone::LANGID_UNDEFINED,
                        'errorDesc' => 'Language ID is not provided in the input.'
                    );
                } else {
                    $response['code'] = 400;
                    $response['body'] = array(
                        'error' => 'SOURCECODE_UNDEFINED',
                        'errorCode' => \Ideone::SOURCECODE_UNDEFINED,
                        'errorDesc' => 'Source code is not provided in the input.'
                    );
                }
            }
        }

        return $response;
    }