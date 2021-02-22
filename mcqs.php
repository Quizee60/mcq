<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MCQs Import Markup</title>
<style type="text/css">
* {
  /* So 100% means 100% */
  box-sizing: border-box;
}
html,body { height:100%; margin: 0px; padding: 0px; }
body{
	background-color:#AAA;
}
.container {
	box-sizing: border-box;
	width: 100%;
	height:100%;
	padding:10px;	
}
.textareaContainer {
    padding: 0px;
	width:100%;
	height: calc(100% - 200px);
}
textarea { 
	color:#FFF;
	background-color: #333;
	width: 100%; 
	height:100%;
	margin: 0; 
	padding: 20px; 
	border-width: 1;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}
</style>
<?php

/*
	https://stackoverflow.com/questions/23826129/regex-expression-for-digit-followed-by-dot

	preformat find and repalce mcq
	Find: ^\d+\.
	replace: ##\r\n@\r\n&&\r\n$0

	find multiple words at the same time: \bTest|\bsql  this mean search form Test or sql
	or find like \b(All above|All are|all bove|All the|All correct)
	find trailing space: [ \t]+$
	find line begine with number and dot. ex "999.": ^\d+\..*$
	find line start with + sign: ^\+.*$
	find ^\tLine.*\d+\.\t -> Ex. 	Line 6: 1.	
	find ^\tLine.*\d+\: ✓

	find line start with + or - and has above: ^(\+|\-)\s.*above
	^(\+|\-)\s.*\b(above|correct|all of above|All above|All are|all bove|All the|All correct)
	^(\+|\-)\s.*\b(all of the above|all of above|All above|All are|All the|All correct)
	^(\+|\-)\s.*$

	Find string start with + or -   =>>>>  ^(\+|\-)\s.*$
	Find string end with   .ccf     =>>>>  .*\.ccf$
	In Notepad++ the search result just select text and right click and choose copy instead using keyboard shortcut
	in order to not copy the line number
*/

function fn_parseMCQ($file_name){
	$path_to_file = $file_name.'.txt';
	/* 
		$file_contents = file_get_contents($path_to_file);
		$file_contents = str_replace("\nH",",H",$file_contents);
		file_put_contents($path_to_file,$file_contents);
	*/
	///////////////////////////////////////////////////////////////////
	/*
		https://stackoverflow.com/questions/16507488/regex-php-match-a-string-start-with-number-and-end-before-number
		preg_replace('/<img(.*?)>/is', '<div class="bilde"><img$1></div>', $saturs);
		
		if (preg_match('#^?\.(.*)$#i', $url) === 1) {
			// Starts with http (case insensitive).
		} 
		
		https://superuser.com/questions/545461/replace-carriage-return-and-line-feed-in-notepad
		-	find \n replace with blank text
		-	find \r replace with \r\n

		✓.
		php file function will get all lines of text
	*/
	//////////////////////////////////////////////////////////////////
	$lines = file($path_to_file);
	$f_lines= []; $nc = 0; //$nc = number of cards
	if($lines === false) return array($nc, $f_lines);
	foreach ($lines as $ln => $line) {
		if(preg_match('/^\d+\./', $line, $match) === 1){
			//if($match[0]) != '1.') ;
			if($ln > 1){
				//$f_lines[] = '##'.chr(13).'@'.chr(13).'&&'.chr(13).'';
				$f_lines[] = '##'.chr(13);
				$f_lines[] = '@'.chr(13);
				$f_lines[] = '&&'.chr(13);
			}
		}
		$f_lines[] = $line;
	}
	if(count($f_lines>0)){
		$f_lines[] = '##'.chr(13);
		$f_lines[] = '@'.chr(13);
		$f_lines[] = '&&'.chr(13);
	}
	
	$nf_lines = []; $tmp = '';
	foreach ($f_lines as $ln => $line) {
		if (substr($line, 0, 1) == '+') {
			$line = '-'.substr($line, 1);
			$tmp  = $line;
		}
		if ($line[0]=='@') {
			$line = $tmp;
			$nc++;
		}
		$nf_lines[] = $line;
		//file_put_contents($file_name.'_NEW.txt', $line, FILE_APPEND);
	}
	return array($nc, $nf_lines);
}

function fn_getCards1($txtsource){
	$cards = explode('&&', $txtsource);
	if(count($cards)>0){
		foreach($cards as $ind=>$card){
			list($t, $a) = explode('##', $card);
		}
	}
}

function fn_getCards($file_name){
	$path_to_file = $file_name.'.txt';
	$lines = file($path_to_file);
	
	$cards = []; $opts = []; $a = ''; $cind = 0;
	if($lines === false) return $cards;
	
	foreach ($lines as $ln => $line) {
		if(preg_match('/^\d+\.\s/', $line, $match) === 1){
			$qn = $match[0];
			$q  = substr($line, strlen($qn));
			$q  = trim($q);
			$qn = (int)$qn;
			$cards[] = array($qn, $q, array(), '');//No.,question, option, answer
			// making first question, as it reach second.
			if($ln > 1){
				$cards[$cind-1][2] = $opts;
				$cards[$cind-1][3] = $a;
				$opts = [];
				$a = '';
			}
			$cind++;
		}
		else{
			if($cind == 0) continue;// skip if it does reach question mark by number d.
			if (substr($line, 0, 1) == '+') {
				$line = substr($line, 1);
				$a    = trim($line);
			}else{
				$line = substr($line, 1);
			}
			$opts[] = trim($line);
		}
	}
	if($cind>0){
		$cards[$cind-1][2] = $opts;
		$cards[$cind-1][3] = $a;
	}
	return $cards;
}

$file_name = '';
$cards  = [];
$nlines = []; $ic = 0;
if(!empty($_GET['fname'])){
	$file_name = $_GET['fname'];
	$cards = fn_getCards($file_name);
	//die(print_r($cards));
	//list($ic, $nlines) = fn_parseMCQ($file_name);
}

?>
</head>
<body>
	<div class="container">
		<form action="mcqs.php">
			<label><span>File Name: </span> 
			<input type="text" name="fname" value="<?php echo $file_name; ?>" style="width:600px;padding:5px 10px" /></label>
			<input type="submit" value="Go" style="padding:5px 10px">
			<br/><hr/>
			<?php echo 'Total: '.count($cards); ?>
			<div class="textareaContainer">
				<textarea class="" cols="80" rows="30"><?php 
				//echo implode("",$nlines);
				
				//usort($array, function($a,$b){ return $a['optionNumber']-$b['optionNumber'];} );
				//sort card by answer
				usort($cards, function($a, $b) {
					return strcmp($a[3], $b[3]);
				});
				foreach($cards as $ind => $card){
					echo $card[1].chr(13);
					$opts = $card[2];
					shuffle($opts);
					foreach($opts as $i=>$v){
						echo "- ".$v.chr(13);
					}
					echo chr(13)."QNo.: ".$card[0].chr(13);
					echo "##".chr(13);
					echo "- ".$card[3].chr(13);
					echo "&&".chr(13);
				}
				?></textarea>
			</div>
		</form>
	</div>
</body>
</html>