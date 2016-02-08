<?php
    namespace API\Submissions;

    require_once __DIR__ . '/../../../include/php/autoload.php';

    function Response($id = null) {
        if($id != null) {
            $output = \Ideone::getOutput($id);
            if(is_array($output)) {
                $response = $output;
                if(isset($_REQUEST['withSource']) && $_REQUEST['withSource'] == 1) {
                    $sourceCode = \Ideone::getSourceCode($id);
                    if(is_string($sourceCode)) {
                        $response['sourceCode'] = $sourceCode;
                    } else {
                        $errorCode = $sourceCode;
                        if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                            $error = 'SYSTEM_ERROR';
                            $errorDesc = 'Some system error occurred, Please try again.';
                        } else if($errorCode == \Ideone::INVALID_SUBM_ID) {
                            $error = 'INVALID_SUBM_ID';
                            $errorDesc = 'Invalid input for the submission id.';
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
                }
                if(isset($_REQUEST['withInput']) || isset($_REQUEST['withLang']) || isset($_REQUEST['withTimestamp'])) {
                    $input = \Ideone::getInputData($id);
                    if(is_array($input)) {
                        if(isset($_REQUEST['withInput']) && $_REQUEST['withInput'] == 1) {
                            $response['stdin'] = $input['stdin'];
                        }
                        if(isset($_REQUEST['withLang']) && $_REQUEST['withLang'] == 1) {
                            $response['langId'] = $input['langId'];
                            $response['langName'] = $input['langName'];
                            $response['langVersion'] = $input['langVersion'];
                        }
                        if(isset($_REQUEST['withTimestamp']) && $_REQUEST['withTimestamp'] == 1) {
                            $response['timestamp'] = $input['timestamp'];
                        }
                    } else {
                        $errorCode = $input;
                        if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                            $error = 'SYSTEM_ERROR';
                            $errorDesc = 'Some system error occurred, Please try again.';
                        } else if($errorCode == \Ideone::INVALID_SUBM_ID) {
                            $error = 'INVALID_SUBM_ID';
                            $errorDesc = 'Invalid input for the submission id.';
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
                }
            } else {
                $errorCode = $output;
                if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR || $errorCode == \Ideone::REDIRECTION_ERROR) {
                    $error = 'SYSTEM_ERROR';
                    $errorDesc = 'Some system error occurred, Please try again.';
                } else if($errorCode == \Ideone::INVALID_SUBM_ID) {
                    $error = 'INVALID_SUBM_ID';
                    $errorDesc = 'Invalid input for the submission id.';
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
        } else {
            if(isset($_REQUEST['sourceCode']) && isset($_REQUEST['langId'])) {
                $sourceCode = $_REQUEST['sourceCode'];
                $langId = $_REQUEST['langId'];
                if(isset($_REQUEST['stdin'])) {
                    $stdin = $_REQUEST['stdin'];
                } else {
                    $stdin = '';
                }
                if(isset($_REQUEST['timeLimit'])) {
                    $time = $_REQUEST['timeLimit'];
                } else {
                    $time = 0;
                }
                $result = \Ideone::getID($sourceCode, $langId, $stdin, $time);
                if(is_string($result)) {
                    $response = array(
                        'id' => $result,
                    );
                } else {
                    $errorCode = $result;
                    if($errorCode == \Ideone::CURL_ERROR || $errorCode == \Ideone::SCRAPE_ERROR || $errorCode == \Ideone::LOGIN_ERROR) {
                        $error = 'SYSTEM_ERROR';
                        $errorDesc = 'Some system error occurred, Please try again.';
                    } else if($errorCode == \Ideone::INVALID_TIME_INPUT) {
                        $error = 'INVALID_TIME_INPUT';
                        $errorDesc = 'Invalid input for timeLimit.';
                    } else if($errorCode == \Ideone::INVALID_LANG_ID) {
                        $error = 'INVALID_LANG_ID';
                        $errorDesc = 'Invalid input for langId.';
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
            } else {
                if(isset($_REQUEST['sourceCode'])) {
                    $response = array(
                        'error' => 'LANGID_UNDEFINED',
                        'errorCode' => \Ideone::LANGID_UNDEFINED,
                        'errorDesc' => 'Language ID is not provided in the input.'
                    );
                } else {
                    $response = array(
                        'error' => 'SOURCECODE_UNDEFINED',
                        'errorCode' => \Ideone::SOURCECODE_UNDEFINED,
                        'errorDesc' => 'Source code is not provided in the input.'
                    );
                }
            }
        }

        return $response;
    }