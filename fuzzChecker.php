<?php

ini_set('max_execution_time', 0);

$LogOutput = array();
$OutputLineBreak = PHP_EOL;

$RequestParams = array(
	'BaseURI' => FALSE,
	'IsVerbose' => FALSE,
	'IsExplicit' => FALSE,
);

if('cli' == php_sapi_name()) {
	$InboundData = getopt('t:ve', array('target:', 'verbose', 'explicit'));
}
else {
	$OutputLineBreak = '<br />'.PHP_EOL;
	$InboundData = $_REQUEST;
}

if(array_key_exists('t', $InboundData)) {
	$RequestParams['BaseURI'] = $InboundData['t'];
}
elseif(array_key_exists('target', $InboundData)) {
	$RequestParams['BaseURI'] = $InboundData['target'];
}

if(array_key_exists('v', $InboundData) || array_key_exists('verbose', $InboundData)) {
	$RequestParams['IsVerbose'] = TRUE;

	# Only check if verbose
	if(array_key_exists('explicit', $InboundData) || array_key_exists('explicit', $InboundData)) {
		$RequestParams['IsExplicit'] = TRUE;
	}
}

do {
	if(empty($RequestParams['BaseURI'])) {
		$LogOutput[] = 'No valid URL found in the "target" field, please provide one.';
		break;
	}

	$RequestParams['BaseURI'] = rtrim($RequestParams['BaseURI'], '/').'/';

	$ListOfPathsToCheck = explode(
			"\n", #
			str_replace(
					array("\r\n", "\r"), #
					"\n", #
					trim(
							file_get_contents('https://raw.githubusercontent.com/Bo0oM/fuzz.txt/master/fuzz.txt')
					)
			)
	);

	$ListOfPathsChecked = array(
		'safe' => array(),
		'unsafe' => array(),
	);

	foreach($ListOfPathsToCheck as $PathToCheck) {
		$FullCheckURI = $RequestParams['BaseURI'].$PathToCheck;

		$ConnectionHandler = curl_init();
		curl_setopt($ConnectionHandler, CURLOPT_URL, $FullCheckURI);
		curl_setopt($ConnectionHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ConnectionHandler);
		$ResponseHTTPCode = curl_getinfo($ConnectionHandler, CURLINFO_HTTP_CODE);
		curl_close($ConnectionHandler);

		if(200 === $ResponseHTTPCode) {
			$ListOfPathsChecked['unsafe'][] = $FullCheckURI;
		}
		else {
			$ListOfPathsChecked['safe'][] = $FullCheckURI;
		}
	}
	unset($ConnectionHandler, $PathToCheck, $FullCheckURI);

	if(empty($ListOfPathsChecked['unsafe'])) {
		$ListOfPathsChecked['unsafe'][] = 'No unsafe points found.';
	}

	if(empty($ListOfPathsChecked['safe'])) {
		$ListOfPathsChecked['safe'][] = 'No safe points found.';
	}

	if($RequestParams['IsVerbose']) {
		$LogOutput[] = 'List of unsafe points:';
	}

	foreach($ListOfPathsChecked['unsafe'] as $UnsafePoint) {
		$Prefix = $RequestParams['IsVerbose'] ? '- ' : '';
		$LogOutput[] = $Prefix.$UnsafePoint;
	}
	unset($UnsafePoint, $Prefix);

	if($RequestParams['IsExplicit']) {
		$LogOutput[] = '';
		$LogOutput[] = 'List of safe points:';

		foreach($ListOfPathsChecked['safe'] as $SafePoint) {
			$Prefix = $RequestParams['IsVerbose'] ? '- ' : '';
			$LogOutput[] = $Prefix.$SafePoint;
		}
		unset($SafePoint, $Prefix);
	}
} while(FALSE);

echo join($OutputLineBreak, $LogOutput);
echo PHP_EOL;
