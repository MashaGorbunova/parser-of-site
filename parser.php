<?php
/**
* Simple parser phone base from website http://www.yaremcha.com.ua/
* author Maria G (email: m.gorbunova@ukr.net)
*/


/**
* getting links from the website http://www.yaremcha.com.ua/
* creating temp-files and saving content from every page.
* @param $path is URL of content
* @param $dir is path to dir where will be create temp-file
* @return void
*/
function getAllLink ($path, $dir) {

	$content = file_get_contents($path); // get content 

	$content = iconv('windows-1251', 'utf-8',  $content); // encoding to utf-8 

	if (!file_exists($dir)) {
		mkdir($dir, 0600); // create dir if it's not exists
	}

	preg_match_all('^<a(.*?)>^', $content, $c); // get all tag <a>

    for ($i=0; $i<count($c[1]); $i++) {
        preg_match_all('^href="(.*?)"^', $c[1][$i], $b); // get all links in tag <a>
        $link[$i] = $b[1][0]; // add.array with links
    }


    for ($i=0; $i<count($link); $i++) {

        if(substr($link[$i], 0, 4) == 'http') {

    	$tmpfname = tempnam($dir, "LIN"); // create temp-file
        $handle_temp = fopen($tmpfname, "w");
        $handle_link = @fopen($link[$i], "r");

        while (!feof($handle_link)) {
            $buffer = fgetss($handle_link, 4096);
            fwrite($handle_temp, iconv('windows-1251', 'utf-8',  $buffer)); // filling temp-file 
        }

        fclose($handle_link);
        fclose($handle_temp);

        getContact($tmpfname);

        }

        else {

    	$tmpfname = tempnam($dir, "LIN"); // create temp-file
        $handle_temp = fopen($tmpfname, "w");
        $handle_link = @fopen('http://www.yaremcha.com.ua/'.$link[$i], "r");

        while (!feof($handle_link)) {
            $buffer = fgetss($handle_link, 4096);
            fwrite($handle_temp, iconv('windows-1251', 'utf-8',  $buffer)); // filling temp-file
        
        }

        fclose($handle_link);
        fclose($handle_temp);

        getContact($tmpfname);
    	
        }
    }
}

/**
* getting contact from temp-file
* creating file in formate .csv, filling it
* and deleting temp-file
* @param $file is saved temp-file
* @return void  
*/
function getContact ($file) {

	if (is_file($file)) {

		$buf = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		for ($i=0; $i<count($buf); $i++) {
			$trim[] = trim($buf[$i]); // remove spaces from beginning and ending of string
		}

		$array = array_diff($trim, array('')); // deleting empty values

		$name = current($array); // getting first element from array. This is name of addressee

	    foreach($array as $value) {

		    if (current($array) == 'Адреса') {

		        $adress = next($array); // getting value with adress

		        if (strrpos(next($array), '+38') >0) {

		            $tel = current($array); // getting value with phone number
	            }
	        }
	
            next($array);
	    }

        if ($adress != '') {

	        $dir = $_SERVER['DOCUMENT_ROOT'].'/parsing/';
	        $file_csv = $dir.'adress_base.csv';
            $tofile = "$name;$adress;$tel\n";
            $bom = "\xEF\xBB\xBF";
            @file_put_contents($file_csv, $bom . $tofile . file_get_contents($file_csv));
        
        } 

        unlink($file); // deleting temp-file
	}

}

/* Incoming data */

// URL of  website
$path = 'http://www.yaremcha.com.ua/';

// path to dir for creating temp-file
$dir_temp = $_SERVER['DOCUMENT_ROOT'].'/parsing/temp/';

/* Implementation script */

$t1 = microtime(true);
getAllLink($path, $dir_temp);
$t2 = microtime(true);
echo "Time for function getAllLink is ".($t2-$t1). " sec ";
