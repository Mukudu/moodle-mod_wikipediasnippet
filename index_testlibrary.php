<?php
/*
    script to test the wikipedia snippet

    see also http://www.ibm.com/developerworks/opensource/library/x-phpwikipedia/index.html?ca=drs-
    http://www.mukudu.net/moodle/mod/wikipediasnippet/wikislurp/?secret=123456789&query=Australia&section=0&output=php


*/

require_once('lib/wikipediasnippet.inc.php');

//$url = 'http://en.wikipedia.org/wiki/Australia#Religion';
$url = "http://en.wikipedia.org/wiki/St._Chad%27s_Cathedral,_Birmingham#History";

//$url = 'http://en.wikipedia.org/wiki/World_War_II#Background';     //DIRECT
//$url = 'http://en.wikipedia.org/wiki/World_War_II#toc';
//$url = 'http://en.wikipedia.org/wiki/Second_world_war#Background';    //REDIRECT
//$url = 'http://en.wikipedia.org/wiki/Second_world_war#toc';
//$url = 'http://en.wikipedia.org/wiki/World_War_II#Citations';

//$url = 'http://en.wikipedia.org/wiki/Port_Line';
//$url = 'http://en.wikipedia.org/wiki/Port_Line#Formation';
//$url = 'http://en.wikipedia.org/wiki/Port_Line#toc';

//$url = 'http://en.wikipedia.org/wiki/Burlesque#Victorian_theatrical_burlesque';
//$url = 'http://en.wikipedia.org/wiki/Burlesque#toc';

//$url = 'http://en.wikipedia.org/wiki/Hermann_Pohlmann#toc';


// change it to a form
// if ( !isset($_GET['snipaddr']) || !$_GET['snipaddr'] ) {
//     echo "die - snipaddr not given in request";
//     die;
// }


$debug = true;
$raw = false;                                //set this for reporting later
$nolinks = false;
$noimages = false;

$wikisnippet = new WikipediaSnippet();              // want debugging
$wikisnippet->setdebugging($debug);                                     //turn debugging on
$wikisnippet->setRawOutput($raw);
$wikisnippet->setProxy('128.243.253.109:8080');
$ctime = (int) 0;
$wikisnippet->setCaching($ctime);  //switch off caching

$content = $wikisnippet->getWikiContent($url,$nolinks,$noimages);

echo "===Start Of Snippet=====";
if ($raw) echo "<pre>";
if (!$wikisnippet->error) {
    if (!$raw) {
        //print out some headers
        echo '<html><head><link rel="stylesheet"
            href="//bits.wikimedia.org/en.wikipedia.org/load.php?debug=false&lang=en&modules=site&only=styles&skin=vector&*"
            type="text/css" media="all" /><body>';
    }
    if ($content) {
        echo $content;
    }else{
        echo 'No content returned';
    }
    if (!$raw) {
        //print out end html tags
        echo '</body></html>';
    }
}else{
    print $wikisnippet->error ."<br/>\n";
}

if ($raw) echo "</pre>";

echo "===End Of Snippet=====";

?>

