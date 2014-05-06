<?php

function request($url,$params=array(),$requestMethod='GET',$jsonDecode=true,$headers=array()) {
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ci, CURLOPT_USERAGENT, '1001 Magazine v1');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ci, CURLOPT_TIMEOUT, 10);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		
		$requestMethod = strtoupper($requestMethod);
		switch ($requestMethod) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if ($params) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
				}
				else {
					curl_setopt($ci, CURLOPT_POSTFIELDS, ''); // Don't know why: if not set,  413 Request Entity Too Large
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if ($params) {
					$url = "{$url}?{$params}";
				}
				break;
			case 'GET':
				if($params) {
					$sep = false === strpos($url,'?') ? '?' : '&';
					$url .= $sep . http_build_query($params);
				}
				break;
			case 'PUT':
				if($params) {
					curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "PUT");                                                                     
					curl_setopt($ci, CURLOPT_POSTFIELDS, $params);                                                                  
				}
				break;
		}
		//$headers[] = "APIWWW: " . $_SERVER['REMOTE_ADDR'];
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
		
		$response = curl_exec($ci);
		$httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$return = array(
			'httpcode' => $httpCode,
			'data' => $httpCode >= 300 ? $response : ($jsonDecode ? json_decode($response,true) : $response),
		);
		//$httpInfo = curl_getinfo($ci);
		curl_close ($ci);
		return $return;
	}
