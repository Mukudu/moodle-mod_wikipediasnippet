<?php

/*

TO D0

1. if required - return text only - DONE
2. remove all formating - class and styles and ids as well - css to be determined by recieving system
3. fix all urls in returned text - only if text only has not been requested
4. strip images if required - rmeber to strip enclosing links if necessary
	
	
	
*/	

require 'simple_html_dom.php';

$pgcontents = '';

#table of content items - optional
$toc_id = '#toc';
$toc_item = 'li';
$toc_href = 'a';
$toc_text = '.toctext';

#information box - optional
$infobox_id = '.infobox';

#fragment
$fragmentstyle = 'mw-headline';

$startcontentidentifier = '.mw-content-ltr';	//wikipedia starts content with div with this class
$endcontentidentifier = '.printfooter';			//wikipedia ends content with this identifier - .XXXXXX = class #XXXXXX = id otherwise just a tag //comments DO NOT count

// Create a DOM object
$html = new simple_html_dom();
$endcontentidentifier = strtolower($endcontentidentifier);			//make sure this is always lower case  - IS THIS A GOOD IDEA???

error_log("Starting.....");

if ($url = $_GET['snipaddr']) {
//	echo 'Hello world - '.$url."<br/>\n";

	//make sure we have a proper url here
	if (!$webaddress = parse_url($url)) {
// 		scheme - e.g. http 
// 		host 
// 		port 
// 		user 
// 		pass 
// 		path 
// 		query - after the question mark ? 
// 		fragment - after the hashmark # 
		die("This is not a proper URL");
	}
	
// 	$url2 = $webaddress['scheme'].'://'.$webaddress['host'];
// 	if (defined($webaddress['port'])) {
// 		$url2 .= ':'.$webaddress['port'];
// 	}
// 	$url2 .= $webaddress['path']; 		
// 	//no fragment because we have some cheats in there
// 	if (defined($webaddress['query'])) {
// 		$url2 .= '?'.$webaddress['query'];
// 	}
	//echo 'Hello world - '.$url2."<br/>\n";
	
	// Load HTML from a URL 
	$html->load_file($url);			//can't see why this will not work with $url2
	if (gettype($html) != 'object') {
		die("Could not load url - $url");			//no resources will have been taken up by a fialed creation of $html
	}
		

	//if there is no bookmark anchor in the text - then get the table of contents
	//an auto bookmark called summary is created for the text above the toc
	if (!$webaddress['fragment']) {
		//lets get the table of contents
		$toc_content = array();
		$toc_content['#preamble'] = 'Preample';
		
		//need to check if in fact there is an info box on the page
		$toc_content['.infobox'] = 'Info Box';
		
		$tocitems = $html->find("$toc_id $toc_item");
		
		foreach ($tocitems as $tocitem) {
// 			<li class="toclevel-1 tocsection-1">
// 				<a href="#Construction_and_early_years">
// 					<span class="tocnumber">1</span> 
// 					<span class="toctext">Construction and early years</span>
// 				</a>
// 			</li>

			//just want the anchor and the plain text
			$toc_content[$tocitem->find($toc_href,0)->href] = $tocitem->find($toc_text,0)->plaintext;
		}
		
		//just for our purposes we will write it out
		//for each
		
		print_r($toc_content);
		
		
	}else{
		//we need to find the fragment and return that
		$extract = array();
		
		//special cases - preamble and infobox
		if ($webaddress['fragment'] == 'preamble') {
			echo '<h1>Preamble</h1>';
			
			//assuming that the first paragraphs in content area are the premable
			
			//first get to content starting point
			if ($startcontentidentifier) {				//if it is defined - otherwise we moan
				if ($start = $html->find($startcontentidentifier)) {
					//get the paragraphs
					$next = $start[0]->find('p',0);
					$extract[] = $next;			//save the 1st paragraph
					
					//now keep walking the dom model until we hit either a table of a header
					//otherwise list of allowable items is longer <p>, <ul>, <ol>, <li>, etc etc
					while($next = $next->next_sibling()) {
						if ((substr(strtolower($next->tag),0,1) == 'h') || (strtolower($next->tag) == 'table')) {
							break;
						}else{
							$extract[] = $next;
						}
					}
				}else{
					$extract[] = "<h1>No Contentstart identifier was found on this page</h1>";
				}
			}else{
				$extract[] = "<h1>Content Start Identifier has not been set</h1>";
			}
		
		}elseif ($webaddress['fragment'] == 'infobox') {
			//echo '<h1>infobox</h1>';
			
			if (!$start = $html->find('.infobox')) {
				$extract[] = "<h1>No information box on this URL - $url</h1>";
			}else{
				$extract[] = $start[0];
			}

		}else{ 
			//first find the fragment area
			$start = $html->find('#'.$webaddress['fragment']);
			
			$next = $start[0]->parent();			//this will be the closest heading <h2> or <h3>
			$sect_tag = strtolower($next->tag);		//level of section we are in <h1>|<h2>|<h3> etc etc
			$sect_level = intval(substr($sect_tag,strlen($sect_tag)-1,1));			//should be just the number
			$extract[] = $next;						//save html content in $extract
			
			//while there is more content at the same level and it is not empty
			// second check makes for quicker exit of loop
			while (($next = $next->next_sibling()) && (strtolower($next->tag) != $sect_tag)) {
		
				//check that we have not gone up a level or two
				if (substr(strtolower($next->tag),0,1) == 'h') {			//we have a header
					$sect_level2=intval(substr($next->tag,strlen($next->tag)-1,1));
					if ($sect_level2 <= $sect_level) {
					 	break;
				 	}
			 	}
				
				//check whether we should be finishing now - if defined
				if ($endcontentidentifier) {				//if it is defined - otherwise works to the end of the dom tree
			        switch(substr($endcontentidentifier,0,1)) {
			            case '.': 
			            	//this is a class	
							if ('.' . strtolower($next->class) == $endcontentidentifier) {
								break;
							}
			            	break;
			            case '#': 
			            	//this is an id
							if ('#' . strtolower($next->id) == $endcontentidentifier) {
								break;
							}
			            	break;
			            default:
			            	//this is a tag
							if (strtolower($next->tag) == $endcontentidentifier) {
								break;
							}
			        }
				}
				//echo "this is next " . $next->plaintext . ' tagged <b>' . $next->attribute . "</b><br/>\n";
				$extract[] = $next;		//save html content in $fagments
			} 
		}
	}
		
	error_log("Finished processing....");	

	$result = join("\n",$extract);
	$html->clear();

	//now we can work with our collected content
	
	error_log("Starting to work with our own content");
	
	$html->load($result);
	if (gettype($html) != 'object') {
		die("Could not load url - $url");			//no resources will have been taken up by a fialed creation of $html
	}
	
			
	//text only - no images, no links etc
	if ($_GET['getTextOnly']) {
		echo $html->plaintext;
	}else {
		//fix all the links in the content - $url2 holds the requested url
		$url2 = $webaddress['scheme'].'://'.$webaddress['host'];
		if (defined($webaddress['port'])) {
			$url2 .= ':'.$webaddress['port'];
		}
		//$url2 .= $webaddress['path']; 
		
		//fix links
		$allhrefs = $html->find('a[href]');
		foreach ($allhrefs as $href) {
			if (stripos($href->href,'://') === false) {			//these already have a fully qualified url
				if (substr($href->href,0,1) == '/') {
					$href->href = $url2 . $href->href;
				}elseif (substr($href->href,0,1) == '#') {
					$href->href = $url2 . $webaddress['path'] . $href->href;
				}else{
					//this is rubbish - need to fix - if last part of path is filename - needs to be removed
					$href->href = $url2 . '/' . $webaddress['path'] . '/' . $href->href;
				}
			}
			//echo "$href" . "<br />\n";
		}

		//default remove all styling and ids  ----
		if ($_GET['noImages']) {
			//strip all image related stuff out of the content
			$allimages = $html->find('img');
			foreach ($allimages as $image) {
				//check if our parent is a link or				
				echo "Found this ". $image->src ."<br/>\n";
				
				$image->parent->outertext = '';
				
				//echo "$href" . "<br />\n";
			}
		}
		
		//strip_tags( string $str [, string $allowable_tags ] ) '<p><a><br>' //just line breakers allowed

		
		echo $html;
	}

	$html->clear();	
	unset($html);		
	echo "<p>END</p>";
	
	
	exit; // don't show the form below
}			//else we just show the form below
	

?>

<html>
	<head>
		<title>Wikipedia Snippet</title>
	</head>
	<body>
		<p>&nbsp;</p>
		<form method="GET">
			Wikipedia URL: <input type="text" name="snipaddr" id="snipaddr" /> <br />
			
			<input type="submit" value="Submit" />
		</form>
	</body>
</html>


