<?php
    $config = include(__DIR__ . '/config.inc');

    /*
     * cURL function which simplifies page fetching operations
     */
	function curl($url, $postdata = NULL, $enableCookies = false, $multipart = false, $header = false, $timeout = 0) {
        $url = str_replace("&amp;", "&", urldecode(trim($url)));
        $cookie = $GLOBALS['config']['tempLocation'] . '/cookie.txt';
        $curl = curl_init();
        $html = NULL;
        $response = NULL;
        $error = NULL;
        $errorno = NULL;

        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);

        if(isset($postdata) && is_array($postdata)) {
            if($multipart) {
                $boundary = "-----------------------------" . floor((float)rand()/(float)getrandmax() * pow(10, 8));
                $encodedData = '';
                foreach($postdata as $key => $value){
                    $encodedData .= "--" . $boundary . "\nContent-Disposition: form-data; name=\"" . $key . "\"\n\n" . $value . "\n";
                }
                $encodedData .= "--" . $boundary . "--";
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=" . $boundary));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $encodedData);
            } else {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postdata)); 
            }
        }

		if($enableCookies) {
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
		}

        if($header) {
            curl_setopt($curl, CURLOPT_HEADER, 1);
        }

        if($GLOBALS['config']['proxy'] != '') {
            curl_setopt($curl, CURLOPT_PROXY, $GLOBALS['config']['proxy']);
        }

        $html = curl_exec($curl);
		$response = curl_getinfo($curl);

		if(!$html) {
            $error = curl_error($curl);
            $errorno = curl_errno($curl);
			curl_close($curl);
            return array("html" => $html, "response" => $response, "error" => $error, "errorno" => $errorno);
        } else {
			curl_close($curl);
        	return array("html" => $html, "response" => $response, "error" => $error, "errorno" => $errorno);
        }
    }