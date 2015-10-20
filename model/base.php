<?php

namespace base;

/**
 * Interface newssource
 * @package base
 */



interface newssource
{
    /**
     * Returns the available feed posts in an array
     *
     * @return array
     */
    public function getItems();
}


interface chairlisting
{
    /**
     * Returns available chairs from a faculty
     *
     * @return array
     */
    public function getAllChairs();


    //TODO can this be done with names instead of numbers as well?
    /**
     * Returns a specific chair from a faculty
     *
     * @param $number
     * @return object
     */
    public function getChair($number);
}


/**
 * Class feedreader
 * @package base
 */
abstract class feedreader implements newssource
{
    /**
     * @var array $posts holds the posts currently available in the feed
     * @var string $feedid unique identifier for the current feed
     * @var boolean $force_get true means no conditional get; necessary for webpages with erroneous last-modified /etags
     * @var string $publicname is a string to prepend the title of each newsentry with
     * @var boolean $downloadqualifier specifies if we are allowed to load data from a remote ressource
     * TIMEOUT a constant defining how long the posts shall be cached (in seconds)
     * CACHEDIR a constant holding the directory of the cached files
     * DOCTYPE type of the document to be cached
     * MAXSTRINGLENGTH when to truncate long strings
     * RESERVEDSPECIAL special value which indicates emptiness
     */
    private $posts = array();
    private $requestdata = self::RESERVEDSPECIAL;
    protected $source = self::RESERVEDSPECIAL;
    protected $feedid = self::RESERVEDSPECIAL;
    protected $force_get = false;
    protected $publicname = "";
    private $downloadqualifier = true;
    const TIMEOUT =  1800;
    const CACHEDIR = "/../cache/";
    const DOCTYPE = "generic";
    const MAXSTRINGLENGTH = 80;
    const RESERVEDSPECIAL = "EMPTY";

    public final function getItems() {
        $this->updateItems();
        return $this->posts;
    }

    public function __construct($publicname, $feedid, $source, $force_get = false) {
        $this->source = $source;
        $this->publicname = $publicname;
        $this->feedid = $feedid;
        $this->force_get = $force_get;
    }

    public function SetDownloadAllowed($input) {
        $this->downloadqualifier = $input;
    }

    /**
     * Converts an ancient c-style errorno into a more modern exception which is catchable through try/catch
     * Used for querypath parsing errors
     *
     * @param $errno integer currently unused
     * @param $errstr string currently unused
     * @param $errfile string currently unused
     * @param $errline integer currently unused
     * @throws \ErrorException
     */
    public function ErrorToExceptionConverter($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    protected function prependText($text) {
        if (empty($this->publicname)) {
            return trim($text);
        }
        else {
            return "[".$this->publicname."] ".trim($text);
        }
    }

    protected final function GetRequestData() {
        return $this->requestdata;
    }

    /**
     * Override erroneous encoding as supplied by the WEBCMS-based pages
     *
     * @return array
     */
    protected final function overrideEncoding() {
        $options = array(
            'convert_from_encoding' => 'iso-8859-1',
        );
        return $options;
    }

    protected abstract function processItems();

    /**
     * This functions truncates text to a given length and removes newline-characters
     *
     * @param $input
     * @return string
     */
    protected final function tidyText($input) {
        $input = trim(preg_replace('/\s+/', ' ', $input)); //remove newlines

        $suffixtext = " ...";
        $suffixtextlength = strlen($suffixtext);

        if (strlen($input) > static::MAXSTRINGLENGTH-$suffixtextlength) {
            return substr($input, 0, static::MAXSTRINGLENGTH-$suffixtextlength).$suffixtext;
        }
        else {
            return $input;
        }
    }

    protected final function SetPostingsToEmpty() {
        //usually something has gone wrong so we set posts to empty here
        $this->posts = array();
    }

    /**
     * Writes a posting to the output-array.
     * Thus ensuring consistent key/value-relations for a single posting in a newssource.
     *
     * @param $date integer A unix timestamp of when the posting was made
     * @param $author string The author of the posting
     * @param $text string The heading of the posting
     * @param $link string An URL to the posting
     * @return array The formatted posting
     */
    protected function AppendToPostings($date, $author, $text, $link) {
        $output = array ("timestamp" => $date, "author" => $author, "text" => $text, "link" => $link);
        $this->WritePostingRaw($output);
    }

    /**
     * Write a single posting to the posts[] array
     * @param $postingdata
     */
    protected final function WritePostingRaw($postingdata) {
        $this->posts[] = $postingdata;
    }

    /**
     * This is basically a replacement for filge_get_contents using cURL-functionality
     * @param $urlToRetrieve string the URL to be retrieved
     * @return mixed the body of the retrieved HTTP-request, false on error
     */
    protected final function GrabFromRemoteUnconditional($urlToRetrieve) {
        $curlhandler = $this->setupCURL($urlToRetrieve, false);
        $body = curl_exec($curlhandler);
        $http_return_code = curl_getinfo($curlhandler, CURLINFO_HTTP_CODE);
        curl_close($curlhandler);

        if ($http_return_code == 200) {
            return $body;
        }
        else {
            return false;
        }
    }

    private final function IsDownloadAllowed() {
        return $this->downloadqualifier;
    }

    /**
     * Holds the internal logic for acquiring new feed data
     *
     * @return null
     */
    private final function updateItems() {
        if ($this->CacheFileAvailable()) {
            if (!$this->IsTimeout()) {
                $this->ReadFromCache();
            }
            elseif ($this->IsDownloadAllowed()) {
                //grabfromremote with conditional get
                $cachefileage = $this->CacheFileAge();
                $etag = $this->GetEtagFromCache();

                if ($this->force_get) {
                    //we must force an unconditional get because the feed does behave weird when attempting otherwise
                    $cachefileage = self::RESERVEDSPECIAL;
                    $etag = self::RESERVEDSPECIAL;
                }

                if (($this->GrabFromRemoteConditional($cachefileage, $etag)) == false) {
                    $this->ReadFromCache();
                }
            }
            else {
                //fallback: read from cache regardless of its age
                $this->ReadFromCache();
            }
        }
        else {
            //unconditional get from remote; fallback to "no output" if that fails
            if (($this->GrabFromRemoteConditional()) == false) {
                $this->SetPostingsToEmpty();
            }
        }
    }

    /**
     * Grab the feed from its remote location
     * Since PHP still has rather poor threading support we do accept simply accept this call to take a while rather
     * than to fork it in the background and return the cache contents for the meantime
     *
     * @param $fileage string The age of the file to compare the Last-Modified header of the remote source against
     * @param $etag string The etag of the resource as it is currently cached (to be compared against If-None-Match)
     * @return null
     */
    private final function GrabFromRemoteConditional($fileage = self::RESERVEDSPECIAL, $etag = self::RESERVEDSPECIAL) {
        $curlhandler = $this->setupCURL($this->source, true);


        //further cURL setup for conditional get
        if ($fileage != self::RESERVEDSPECIAL) {
            curl_setopt($curlhandler, CURLOPT_TIMECONDITION, CURL_TIMECOND_IFMODSINCE);
            curl_setopt($curlhandler, CURLOPT_TIMEVALUE, $fileage);
        }
        if ($etag != self::RESERVEDSPECIAL) {
            curl_setopt($curlhandler, CURLOPT_HTTPHEADER, array('If-None-Match: '.$etag));
        }

        //execute the request
        $response = curl_exec($curlhandler);
        $info = curl_getinfo($curlhandler);
        curl_close($curlhandler);
        $http_return_code = $info['http_code'];
        $headers = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']); //dont rely on $info['download_content_length'] here since Fricke is sending out a wrong Content-Length.

        //TUD sends out 200 even if its actually a 304, hence the body-check
        if ($http_return_code == 304 or ($http_return_code == 200 and $body == false)) {
            //content is up to date; stay with the cache
            touch($this->GetCacheFilename());
            $this->ReadFromCache();
            return true;
        }
        elseif ($http_return_code == 200) {
            //content is not up to date; perform update
            $this->requestdata = $body;

            //get the etag from the header
            //we are intentionally not using http_parse_headers here - because a pecl install for a single function is overkill
            if ((preg_match("/etag: (.*)/i",$headers,$returnvalue)) === 1) {
                $etag = $returnvalue[1];
                //leave the etag as it is -- dont remove any surrounding quotes or similar
                $etag = trim($etag);
            }

            $this->processItemsWithSafeFallback();
            $this->WriteToCache($etag);
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * This is a wrapper for processItems and causes E_RECOVERABLE_ERROR to be silently discarded
     * Those errors are usually generated if the htmlqp() lookup fails for some reason
     */
    private function processItemsWithSafeFallback() {
        set_error_handler(array($this, 'ErrorToExceptionConverter'), E_RECOVERABLE_ERROR);
        try {
            $this->processItems();
        }
        catch (\ErrorException $e) {
            $this->SetPostingsToEmpty();
        }
    }

    /**
     * Perform the generic part of a cURL setup
     *
     * @param $urlToRetrieve string the URL to be retrieved by a subsequent curl_exec()
     * @param $returnHeader bool whether to include the header in the response as well
     * @return resource The handler to the current curl instance
     */
    private final function setupCURL($urlToRetrieve, $returnHeader) {
        $curlhandler = curl_init();
        curl_setopt($curlhandler, CURLOPT_URL, $urlToRetrieve);
        curl_setopt($curlhandler, CURLOPT_HEADER, $returnHeader);
        curl_setopt($curlhandler, CURLOPT_NOBODY, false);
        curl_setopt($curlhandler, CURLOPT_TIMEOUT, 10);
        curl_setopt($curlhandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlhandler, CURLOPT_ENCODING, ""); //send all supported encoding types
        curl_setopt($curlhandler, CURLOPT_USERAGENT, 'TUDnewsscraperbot/0.2 (+http://github.com/morido/tudnewsscraper)');

        return $curlhandler;
    }

    /**
     * Returns the filename to be used for the Cache file
     * @return string
     */
    private final function GetCacheFilename() {
        return realpath(NULL).static::CACHEDIR.static::DOCTYPE."_".$this->feedid.".cache";
    }

    /**
     * Returns true if the Cache File is present on disk, false otherwise
     * @return bool
     */
    private final function CacheFileAvailable() {
        return file_exists($this->GetCacheFilename());
    }

    /**
     * Return the time the cache file was last modified
     * @return int
     */
    private final function CacheFileAge() {
        return filemtime($this->GetCacheFilename());
    }

    /**
     * Returns true if the cache-file is too old
     * @return bool
     */
    private final function IsTimeout() {
        if (time() - $this->CacheFileAge() >= static::TIMEOUT) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Reads in the postings from the cache file
     */
    private final function ReadFromCache() {
        $output = unserialize(file_get_contents($this->GetCacheFilename()));
        $this->posts = $output->posts;
    }

    /**
     * Reads in the etag from the cache file for use with a conditional get (HTTP 304)
     * @return mixed
     */
    private final function GetEtagFromCache() {
        $output = unserialize(file_get_contents($this->GetCacheFilename()));
        return $output->etag;
    }

    /**
     * Writes data into the Cache file
     * @param $etag string The etag that was returned by the last request
     */
    private final function WriteToCache($etag) {
        //make sure we are not interfering with other parallel calls
        clearstatcache();
        touch($this->GetCacheFilename());

        $output = new \stdClass(); //sort of a struct...
        $output->etag = $etag;
        $output->posts = $this->posts;
        file_put_contents($this->GetCacheFilename(),serialize($output), LOCK_EX);
    }
}


abstract class feedsorter implements newssource {
    /**
     * MAXFRESHFEEDS how many feeds may be grabbed from remote during this cycle. All feeds that exceed this limit will
     * be read in from cache, if possible
     */
    const MAXFRESHFEEDS =  1;

    private $feeds;
    private $itemsToReturn;
    private $currentDownloads = 0;

    public function __construct($feeds, $itemsToReturn) {
        $this->feeds = $feeds;
        $this->itemsToReturn = $itemsToReturn;
    }

    public function getItems() {
        //shuffle the feeds so we make sure that caches get updated in an equally likely manner
        shuffle($this->feeds);

        //Read out all available postings
        $output = array();
        foreach ($this->feeds as $feed) {
            $feed->SetDownloadAllowed($this->downloadAllowed());
            $postings = $feed->getItems();
            foreach ($postings as $posting) {
                $output[] = $posting;
            }
        }

        return $this->sortItems($output, $this->itemsToReturn);
    }

    abstract protected function sortItems($items, $itemsToReturn);

    /**
     * This ensures that only MAXFRESHFEEDS are downloaded from a remote site during each cycle. Since the feeds are in
     * random order for each invocation of the script this updates them "sequentially"
     *
     * @return bool
     */
    private function downloadAllowed() {
        if ($this->currentDownloads < static::MAXFRESHFEEDS) {
            $this->currentDownloads++;
            return true;
        }
        else {
            return false;
        }
    }
}

abstract class chairreturner implements chairlisting {
    protected $chairs = array();

    public function getAllChairs() {
        return $this->chairs;
    }

    public function getChair($number) {
        if ($number < count($this->chairs)) {
            return $this->chairs[$number];
        }
        else {
            return false;
        }
    }
}

?>