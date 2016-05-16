<?php

require_once __DIR__.'/../src/linkify.php';

$debug = false;

if (!empty($argv[1])) {
	$files = array(__DIR__.'/data/'.$argv[1].'.tests.txt');
}
else {
	$files = glob(__DIR__.'/data/*.tests.txt');
}

// make it easier to write tests
$options = array(
	'rel_nofollow' => false,
	'target_blank' => false,
);
$linkifier = new alsvanzelf\linkify\linkify($options);

$number = 0;
$ok     = 0;
$fail   = 0;
$done   = 0;

foreach ($files as $file) {
	if (count($files) > 1) {
		echo "\033[97m\033[44m".basename($file)."\033[49m\033[39m".PHP_EOL;
	}
	
	$tests = file($file, FILE_IGNORE_NEW_LINES);
	$expected = null;
	
	$expected_file = str_replace('.tests.txt', '.expected.txt', $file);
	if (file_exists($expected_file) === true) {
		$expected = file($expected_file, FILE_IGNORE_NEW_LINES);
	}
	
	foreach ($tests as $index => $test) {
		// allow comments inside the test file
		if ($test === '') {
			continue;
		}
		if (strpos($test, '# ') === 0) {
			if (count($files) === 1) {
				echo "\033[97m\033[44m".substr($test, 2)."\033[49m\033[39m".PHP_EOL;
			}
			continue;
		}
		
		$number++;
		echo $number;
		
		$actual = $linkifier->linkify($test);
		
		if ($expected === null) {
			$done++;
			echo ' Done'.PHP_EOL;
			
			if ($debug) {
				echo 'test:     '.$test.PHP_EOL;
				echo "result:   ".$actual.PHP_EOL;
			}
		}
		elseif ($actual === $expected[$index]) {
			$ok++;
			echo " \033[42m\033[90mOK\033[49m\033[39m".PHP_EOL;
			
			if ($debug) {
				echo 'test:     '.$test.PHP_EOL;
				echo "\033[42m\033[90mresult:\033[49m\033[39m   ".$actual.PHP_EOL;
			}
		}
		else {
			$fail++;
			echo " \033[97m\033[41mFAIL\033[49m\033[39m".PHP_EOL;
			
			if ($debug) {
				echo 'test:     '.$test.PHP_EOL;
				echo 'expected: '.$expected[$index].PHP_EOL;
				echo "\033[97m\033[41mactual:\033[49m\033[39m   ".$actual.PHP_EOL;
			}
		}
		
		if ($debug) {
			echo PHP_EOL;
		}
	}
}

echo PHP_EOL.'-----'.PHP_EOL;
echo ($fail > 0) ? "\033[97m\033[41m" : "\033[42m\033[90m";
echo 'Tests: '.$number.', OK: '.$ok.', FAIL: '.$fail.', UNVERIFIABLE: '.$done;
echo "\033[49m\033[39m".PHP_EOL;
