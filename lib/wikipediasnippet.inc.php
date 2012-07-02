 <?php

/*
    wikipedia php library

    libary to communicate with wikipedia via api.php and index.php
    to allow php scripts to obtain wikipedia pages

    object orientated??

    using xml format due to protability - php formats proving unreliable - especially serialisation


    TODO : CACHING

*/

define('USERAGENT','Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
// toc      = http://en.wikipedia.org/w/api.php?action=parse&prop=sections&format=xml&page=Seychelles
// preamble = http://en.wikipedia.org/w/api.php?format=xml&action=query&rvprop=content&prop=revisions&titles=Greece&rvsection=0 + regex
// infobox  = http://en.wikipedia.org/w/api.php?format=xml&action=query&rvprop=content&prop=revisions&titles=Greece&rvsection=0 + regex

define('API_CONTENT_URL', 'http://en.wikipedia.org/w/api.php?format=xml&action=query&rvprop=content&prop=revisions');
define('API_HTMLCONTENT_URL', 'http://en.wikipedia.org/w/api.php');
define('API_TOC_URL','http://en.wikipedia.org/w/api.php?action=parse&prop=sections&format=xml&page=');
define('WIKI_CONTENT_URL','http://en.wikipedia.org/wiki/');
define('CACHE_TIME', (60 * 60 * 24 * 10)); // 10 Days default - raw is url#fragment html is url+'noimages=?'+'nolinks=?'+#fragment - make it 0 for no caching

require_once('PhpCache.php');               //caching library

class WikipediaSnippet {

    private $debugging = 0;
    private $cachingenabled = false;

    public $title = '';
    public $rawtitle = '';
    public $url = '';
    public $pageid = 0;
    public $snippets = array();         // including infobox, preamble, #sections
    public $toc = '';

    //below need to become private and are to accessed via access methods
    public $rawoutput = false;
    public $content = '';
    public $error = '';


    // create a wikipedia snippet object
    // pass in the wikipedia url
    function __construct() {
        //do nothing we should be set up OK.
        if (CACHE_TIME !== 0) {
            $this->cachingenabled = true;
        }

    }

    function __destruct() {
        //do I need this???
    }

    //determine if the caller just wants wiki text or xml in the case of the toc
    public function setRawOutput($otype = false) {
        $this->rawoutput = $otype;
    }

    public function getWikiContent($url, $nolinks=false, $noimages=false) {
        $noerror = true;
        $result = '';

        if ($this->debugging) error_log('DEBUG mode - switch switch off in production environments');

        $webaddress = parse_url($url);   //break up the url into parts - scheme - e.g. http ,host,port,user,pass,path,query - after the question mark ?,fragment - after the hashmark

        if (!$webaddress) { //
            $this->_raiseError('This is not a proper URL');
            $noerror = false;
        }elseif (!$webaddress['fragment']) {
            //snippet must always be specified - we do not want everyone just taking full pages of wikipedia do we??
            $this->_raiseError('A URL fragment must be specified - otherwise call wikipedia directly');
            $noerror = false;
        }else{
            // we need the raw title - proper title will come from wikipedia
            $this->rawtitle = basename($webaddress['path']);
            if ($this->debugging) error_log('Raw Title computed to be ' . $this->rawtitle);

            $this->url = $webaddress['scheme'].'://'.$webaddress['host'].$webaddress['path'];

            $type = 'html';
            if ($this->rawoutput) $type = 'raw';

            // make up a caching url // this means rapid return of formatted html but alos
            // means the potential of 5 cached objects for each url#fragment
            if (($nolinks) || ($noimages)) {
                $cacheparams = array();
                if ($nolinks) {
                    $cacheparams[] = 'nolinks=1';
                }
                if ($noimages) {
                    $cacheparams[] = 'noimages=1';
                }

                $cacheurl = $this->url. '?' . join('&',$cacheparams) . '#' .$webaddress['fragment'];

            }else{
                $cacheurl = $url;
            }

            //check in cache
            if (!$result=$this->_cache_get($cacheurl,$type)) {                  //if it is not in the cache

                // first we need to get a table of contents
                // this may be cached locally and so save us some time
                $toc = $this->_get_toc($url);                   //an array that we can use in php - xml in $this->toc  // could be empty

                //use the toc to determine the section we want if not the toc itself
                if ($webaddress['fragment'] != 'toc') {                     //is this the toc?
                    if (!$section=$toc[$webaddress['fragment']]) {          //if not, then we want a valid sestion
                        // otherwise check for special fragments or no toc sections
                        if ($webaddress['fragment'] == 'infobox' || $webaddress['fragment'] == 'preamble' && (count($toc) == 0)) {
                            $section = 0;
                        }
                    }

                    // create the request URL for the wikipedia API
                    $geturl = API_CONTENT_URL . '&titles=' . $this->rawtitle . "&rvsection=$section";
                    if ($this->debugging) error_log("Content API call being made to '$geturl'");

                    if ($response = $this->_getWikiPage($geturl)) {             //make the call
                        //raw content is wrapped in xml
                        if ($this->debugging) error_log("Non Error response returned - making XML Object");

                        if ($response = $this->_response2XML($response)) {          // turn the reponse into an XML object to make it easier to work with

                            //check to see that our wikipedia request was good - actual title will be in .....

                            //extract the page info
                            foreach ($response->query->pages->page->attributes() as $a => $b) {
                                switch (strtolower($a)) {
                                    case 'title':
                                        $this->title = $b;
                                        break;
                                    case 'pageid':
                                        $this->pageid = $b;
                                        break;
                                }
                            }

                            $result = $response->query->pages->page->revisions->rev;

                            //TODO Deal with Section 0 in raw form
                            if ($section == 0) {
                                //not doing this by regex - too bloody hard
                                $lines = explode("\n",$result);
                                $content = array();
                                $inbox = false;
                                foreach ($lines as $line) {
                                    if (stripos( $line, '{{infobox' ) === 0) {
                                        $inbox = true;
                                    }elseif (strpos($line,'{{') === 0) {            //ignore all other formating stuff before the preamble
                                        continue;
                                    }elseif ($line == '}}') {                       //end infobox
                                        if ($webaddress['fragment'] == 'infobox') {
                                            $content[] = $line;
                                        }
                                        $inbox = false;
                                        continue;
                                    }

                                    if (($inbox) && ($webaddress['fragment'] == 'infobox')) {
                                        $content[] = $line;
                                    }elseif (!$inbox && ($webaddress['fragment'] != 'infobox')) {
                                        $content[] = $line;
                                    }
                                }
                                $result = implode("\n",$content);
                            }

                            //here we parse out all the stuff we do not need
                            $result = $this->_CleanupRaw($result,$nolinks,$noimages);

                            //process raw wiki content here
                            if (!$this->rawoutput) {
                                //we want html and we will ask wikipedia to do the work for us
                                //?format=xml&action=parse&text=

                                //this taken out to handle post encoding for wikipedia
    //                          $fields = array(
    //                              'action' => 'parse',
    //                              'prop' => 'text',
    //                              'format' => 'xml',              //undocumented on the wikimedia site
    //                              'text' => urlencode($result),
    //                          );

                                $newfields = 'action=parse&prop=text&format=xml&text='.urlencode($result);
                                $response= $this->_response2XML($this->_getWikiPage(API_HTMLCONTENT_URL,$newfields));

                                //extract the html and unencode it
                                $result = html_entity_decode($response->parse->text);

                                $result = $this->_CleanupHTML($result);
                                //cache the html if we have had
                            }
                            $this->content = $result;  //we're done
                        }
                    }
                }else{
                    // we deal with toc stuff here
                    if (!empty($toc)) {         //we have a valid table of contents
                        //TODO turn $toc into html for our caller
                        // would love to let wikpedia do all the hard work but html tocs are not returned

                        //so here goes
                        $toc_html = array();

                        ///// BEN FIX THIS CODE /////
                        if ($toc_obj = $this->_response2XML($this->toc)) {          // get a copy
                             //now we make it into a php array for later code
                             //<s toclevel="1" level="2" line="History" number="1" index="1" fromtitle="Seychelles" byteoffset="4678" anchor="History"/>
                             $last_level = 1;               //keep track of toc levels
                             $toc_html[] = '<ul>';          //start html list
                             //$this->url
                             foreach ($toc_obj->parse->sections->s as $section) {
                                 $linknumber = 0;
                                 $linktext = '';
                                 foreach ($section->attributes() as $a => $b) {
                                     switch (strtolower($a)) {
                                         case 'toclevel':
                                            //echo "'$a' = '$b' Level is at '$last_level'<br/>\n";
                                            if (intval($b) > $last_level) {         //start a new level list
                                                $toc_html[] = '<ul>';
                                            }
                                            if (intval($b) < $last_level) {     //end old level
                                                $toc_html[] = '</ul>';
                                            }
                                                $last_level = intval($b);
                                            break;
                                         case 'number':
                                            $linknumber = $b;
                                            break;
                                         case 'anchor':
                                            $linktext = $linktext . '<a href="'. $this->url .'#' . $b . '">' . $b . '</a>';
                                            break;
                                      }
                                 }
                                 $toc_html[] = "<li>$linknumber. $linktext</li>";
                             }
                             $toc_html[] = '</ul>';
                        }
                        $result = implode("\n",$toc_html);
                    }else{
                        $this->_raiseError('There does not appear to be a table of contents on this page');
                    }
                }
                //
                //cache the results  - raw and html - this is the specific stuff
                //
                if ($result) {
                    $this->_cache_page($cacheurl,$type,$result);
                }
            }
        }
        return $result;
    }

    //
    // this function is to clean up basic html & fix up internal links then make all links open in a new window
    //
    private function _CleanupHTML($html) {
        //get rid of the edit link
        //<span class="editsection">[<a href="/w/index.php?title=API&action=edit&section=1" title="Edit section: Dunciad and Moral Essays">edit</a>]</span>
        $html= preg_replace('¬<span class="editsection"(.*?)</span>¬i','',$html);

        //fixup all intenenal wikpedia links
        $hrefpattern = '¬/wiki/¬i';
        $html = preg_replace($hrefpattern, WIKI_CONTENT_URL, $html);

        //fix up all links - open in new window
        $html =  preg_replace('¬<a href¬i','<a target="blank" href',$html);

        return $html;

    }

    //
    //this function cleans up the raw wiki text - removing images and links if required
    //
    private function _CleanupRaw($wikitext,$nolinks,$noimages) {

        //remove all the ref stuff - will removes citations embedded in those tags
        $wikitext= preg_replace('¬<ref(.*?)</ref>¬i','',$wikitext);
        $wikitext= preg_replace('¬<ref(.*?)/>¬i','',$wikitext);

        // images
        $images = array();
        $imgpattern = '/(\[\[(File|Image):.+]]\n)/';

        //if no images are required - we quitely remove them
        if ($noimages) {
            $wikitext= preg_replace($imgpattern,'',$wikitext);
            $images = array();          //avoid anything relying on this later
        }

        //links - strip out if not wanted
        if ($nolinks) {
            $links = array();
            $linkpattern = '/\[\[(.+?)]+/';

            //we tokenise images to avoid being caught by the next lot of regexs
            // OUR TOKEN IS $IMAGE_[?]$
            if (preg_match_all($imgpattern,$wikitext,$images)) {
                $images = $images[1];
                for ($i = 0; $i < count($images); $i++) {
                    $token = '$IMAGE_'.$i.'$';
                    $wikitext=str_replace($images[$i],$token,$wikitext);
                }
            }

            //remove the brackets
            preg_match_all($linkpattern,$wikitext,$links);          //find links

            //keep the description if one is set otherwise use the link text
            for ($i =0; $i < count($links[1]); $i++) {
                $tmp = explode('|',$links[1][$i]);
                if (count($tmp) > 1) {                              //avoiding PHP warnings
                    $links[1][$i] = $tmp[1];
                }else{
                    $links[1][$i] = $tmp[0];
                }
            }

            //now just replace each link with its text
            for ($i =0; $i < count($links[0]); $i++) {
                $wikitext = str_replace(($links[0][$i]),($links[1][$i]),$wikitext);
            }

            //TODO
            //should replace any http:// etc text which will automatically made into links by the wikipedia parsers

            //now if we have any tokens we replace them with the images we saved
            if (count($images)) {
                for ($i = 0; $i < count($images); $i++) {
                    $token = '$IMAGE_'.$i.'$';
                    $wikitext=str_replace($token,$images[$i],$wikitext);
                }
            }

        }
        return $wikitext;
    }

    //function to create an XML object from wikipedia xml responses
    private function _response2XML($xml) {

        if ($this->debugging) error_log("XML recasting in progress");

        if ($xml) {
            libxml_use_internal_errors(true);                                   //we want to catch any errors

            if (!$xml = simplexml_load_string($xml)) {                          //turn into xml object
                $xml = '';                      //redefine it
                if ($xml_errs=libxml_get_errors()) {
                    $this->_raiseError(count($xml_errs) . " XML Parsing errors, view error log for details");
                    error_log(print_r($xml_errs,true));
                }else{
                    $this->_raiseError('Undefined error parsing response');
                    if ($this->debugging) error_log($msg);
                }
            }
        }

        if ($this->debugging) error_log("XML being returned");

        return $xml;
    }


    // function to report Errors
    private function _raiseError($errmsg) {
        $this->error=$errmsg;
        if ($this->debugging) error_log($errmsg);
    }


    // function to get the table of contents for the wikipage
    // the toc is cached for faster access and a copy of the xml is
    // always put in the objects toc property
    private function _get_toc($url) {
        if ($this->debugging) error_log("Getting Table of Contents");

        $toc_list = array();
        $type = 'toc';

        if (!$tocxml = $this->toc) {                    //see if we have it already
            $toc_url = API_TOC_URL . $this->rawtitle;
            if (!$tocxml = $this->_cache_get($url,$type)) {     //check in cache
                //if not let's get from wikipedia
                // toc      = http://en.wikipedia.org/w/api.php?action=parse&prop=sections&format=xml&page=Seychelles
                if ($this->debugging) error_log("API call being made to '$toc_url'");

                if ($tocxml = $this->_getWikiPage($toc_url)) {
                    //raw content is wrapped in xml
                    if ($this->debugging) error_log("Non Error response returned, XML being turned into object");

                    if ($toc_obj = $this->_response2XML($tocxml)) {
                        //here we check that we infact have a reasonable response
                        //usually errors will have no setions and the title will be different
                        if (count($toc_obj->parse->sections->s) == 0) && ($toc_obj->parse->attributes['title'] != $this->rawtitle) {
                            //fix the title and url
                            $this->rawtitle = $toc_obj->parse->attributes['title'];
                            $toc_url = API_TOC_URL . $this->rawtitle;

                            if ($this->debugging) error_log("Error in response - title is now ".$this->rawtitle ."' attempting to correct.");
                            //try and get corrected page - breaks everything if it fails
                            $toc_obj = $this->_response2XML($tocxml = $this->_getWikiPage($toc_url));
                        }

                         if ($toc_obj) {
                            //now we make it into a php array for later code
                             //<s toclevel="1" level="2" line="History" number="1" index="1" fromtitle="Seychelles" byteoffset="4678" anchor="History"/>

                             foreach ($toc_obj->parse->sections->s as $section) {
                                 $anchor = '';
                                 $xindex = 0;  //catch any errors
                                 foreach ($section->attributes() as $a => $b) {
                                     switch (strtolower($a)) {
                                         case 'anchor':
                                            $anchor = $b;
                                            break;
                                         case 'index':
                                            $xindex = $b;
                                            break;
                                     }

                                     if ($anchor && $xindex) {
                                         $toc_list["$anchor"] = "$xindex";
                                         break;
                                     }
                                 }
                             }
                            $this->toc = $toc_obj;
                            //cache our xml
                            $this->_cache_page($url,$type,$tocxml);
                        }else{
                            if ($this->debugging) error_log("Error - XML Object not defined");
                        }
                    }
                }
            }
        }
        return $toc_list;

    }

    //
    //  default with no params is true - otherwise true or false - anyother params turns it off i.e. false
    //
    public function setdebugging($on_off=true) {
        if (($on_off === false) || ($on_off === true)) {
            $this->debugging = $on_off;
        }else{
            $this->debugging=false;
        }
    }
    // function to retrieve wikipedia responses
    // $type can be html,toc, or raw
    private function _cache_get($url,$type) {
        if ($this->debugging) error_log("Checking cache");
        $result = '';

        if ($this->cachingenabled) {            //if caching is on
            $cache = new PhpCache( $url, CACHE_TIME, $type );
            if ( $cache->check() ) {
                if ($this->debugging) error_log("Content found in cache - returning");
                $result = $cache->get();
                $result = $result['data'];
            }
        }

        return $result;
    }

    //
    // function will cache an object on disk so that http subsequent calls do not
    // need to go back to wikipedia - only call this for new/replacement objects
    //
    private function _cache_page($url,$type,$content) {
        if ($this->debugging) error_log("Caching routine starts");
        $noerror = true;

        if ($this->cachingenabled) {            //if caching is on
            if ($this->debugging) error_log("Caching results");
            $cache = new PhpCache( $url, CACHE_TIME, $type );
            $cache->set(
                array(
                    'url'=>$url,
                    'data'=>$content
                )
            );
        }
        return $noerror;
    }

    //
    // function to get the page from wikipedia and check for errors before returning xml object
    //
    private function _getWikiPage($url,$postFields='') {
        return(_getPagefromWikimedia($url,$postFields));
    }


    //
    //  function to get the raw content from wikipedia
    //
    private function _getPagefromWikimedia($url,$postFields='') {

        if ($this->debugging) error_log("Making cURL HTTP Request");
        $session = curl_init($url);

        curl_setopt( $session, CURLOPT_URL, $url );
        curl_setopt( $session, CURLOPT_USERAGENT, USERAGENT);               //wikipedia insists on a useragent
        curl_setopt( $session, CURLOPT_HEADER, false );

        //if we need to set a proxy.check in the environment
//      if (HTTPPROXY) {
            curl_setopt( $session, CURLOPT_PROXY, '128.243.253.109:8080');
//      }

        curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1 );
        if (!empty($postFields)) {
            curl_setopt( $session, CURLOPT_HTTPHEADER, array('Expect:'));           //workaround for error caused by a wikipedia squid being a HTTP1.0 device -
                                                                                    //http://serverfault.com/questions/107813/why-does-squid-reject-this-multipart-form-data-post-from-curl
            curl_setopt( $session, CURLOPT_POST, 1);
            curl_setopt( $session, CURLOPT_POSTFIELDS, $postFields );
        }

        if ($this->debugging) error_log("Starting request");

        $result = curl_exec( $session );
        if ($err = curl_error($session)) {
            $err = "HTTP request error - $err";
            if ($this->debugging) error_log($err);
            $this->error = $err;
        }

        if ($this->debugging){
            $info = curl_getinfo($session);
            error_log('cURL Info: '. print_r($info, true));     //write to logs
        }

        curl_close( $session );

        return $result;
    }

}





?>