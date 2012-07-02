<?php
/*
	script to test the wikipedia snippet

	see also http://www.ibm.com/developerworks/opensource/library/x-phpwikipedia/index.html?ca=drs-
	http://www.mukudu.net/moodle/mod/wikipediasnippet/wikislurp/?secret=123456789&query=Australia&section=0&output=php
	
	
*/

require_once('lib/wikipediasnippet.inc.php');

$debug = true;
//$url = 'http://en.wikipedia.org/wiki/Australia#Religion';
$url = 'http://en.wikipedia.org/wiki/St._Chad%27s_Cathedral,_Birmingham#preamble';
//$url = 'http://en.wikipedia.org/wiki/Port_Line';
//$url = 'http://en.wikipedia.org/wiki/Burlesque#Victorian_theatrical_burlesque';

#$url = 'http://en.wikipedia.org/wiki/Port_Line#Formation';

// if ( !isset($_GET['snipaddr']) || !$_GET['snipaddr'] ) {
//     echo "die - snipaddr not given in request";
//     die;
// }

$wikisnippet = new WikipediaSnippet();				// want debugging 
$wikisnippet->setdebugging($debug);										//turn debugging on
$raw = true;								//set this for reporting later
$wikisnippet->setRawOutput($raw);
$content = $wikisnippet->getWikiContent($url,true,true);

if ($raw) echo "<pre>";
if (!$wikisnippet->error) {
	if (!$raw) {
		//print out some headers
		echo '<html><head><link rel="stylesheet" href="//bits.wikimedia.org/en.wikipedia.org/load.php?debug=false&amp;lang=en&amp;modules=site&amp;only=styles&amp;skin=vector&amp;*" type="text/css" media="all" /><body>';
	}
	echo $content;
	if (!$raw) {
		//print out end html tags
		echo '</body></html>';
	}
}else{
	print $wikisnippet->error ."<br/>\n";
}

if ($raw) echo "</pre>";


echo "Ended";


//<title>Wikimedia Error</title>


?>