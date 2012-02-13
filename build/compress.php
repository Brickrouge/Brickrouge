<?php

$src = $_SERVER['argv'][1];
$dst = $_SERVER['argv'][2];

$ch = curl_init('http://marijnhaverbeke.nl/uglifyjs');

curl_setopt_array
(
	$ch, array
	(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query
		(
			array
			(
				'js_code' => file_get_contents($src),
				'utf8' => 1
			)
		),

		CURLOPT_RETURNTRANSFER => true
	)
);

$rc = curl_exec($ch);

if ($rc !== false)
{
	file_put_contents($dst, $rc);
}

echo 'Compressed "' . $src . '" to "' . $dst . '"' . PHP_EOL;