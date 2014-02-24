<?php

namespace base;

/**
 * Interface newssource
 * @package base
 */

//set timeout for get_file_contents to 10 seconds
ini_set('default_socket_timeout', 10);



interface newssource
{
    /**
     * Returns the available feed posts in an array
     *
     * @return array
     */
    public function getItems();

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
     * @var boolean $downloadqualifier specifies if we are allowed to load data from a remote ressource
     * TIMEOUT a constant defining how long the posts shall be cached (in seconds)
     * CACHEDIR a constant holding the directory of the cached files
     * DOCTYPE type of the document to be cached
     * MAXSTRINGLENGTH when to truncate long strings
     */
    protected  $posts = array();
    protected $feedid = "";
    private $downloadallowed = true;
    const TIMEOUT =  1800;
    const CACHEDIR = "/../cache/";
    const DOCTYPE = "generic";
    const MAXSTRINGLENGTH = 80;

    public final function getItems() {
        $this->updateItems();
        return $this->posts;
    }

    protected function __construct($feedid) {
        $this->feedid = $feedid;
        //$this->updateItems();
    }

    public function SetDownloadAllowed($input) {
        $this->downloadallowed = $input;
    }

    /**
     * Holds the internal logic for acquiring new feed data
     *
     * @return null
     */
    protected final function updateItems() {
        if (!$this->CacheFileAvailable()) {
            $this->GrabFromRemote(false);
        }
        elseif (!$this->IsTimeout()) {
            $this->ReadFromCache();
        }
        elseif (!$this->downloadallowed) {
            $this->ReadFromCache();
        }
        else {
            //refresh cache and allow fallback to cache if refresh is impossible
            $this->GrabFromRemote(true);
        }
    }


    /**
     * Returns true if the cache-file is too old
     *
     * @return bool
     */
    protected final function IsTimeout() {
        $fmtime = @filemtime($this->getCacheFilename());

        if (!$fmtime or (time() - $fmtime >= static::TIMEOUT)){
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Returns the filename to be used for the Cache file
     * @return string
     */
    protected final function getCacheFilename() {
        return realpath(NULL).static::CACHEDIR.static::DOCTYPE."_".$this->feedid.".cache";
    }

    protected abstract function init();
    protected abstract function processItems();

    /**
     * This functions truncates text to a given length and removes newline-characters
     *
     * @param $input
     * @return string
     */
    protected function tidyText($input) {
        $input = trim(preg_replace('/\s+/', ' ', $input)); //remove newlines

        if (strlen($input) > static::MAXSTRINGLENGTH-4) {
            return substr($input, 0, static::MAXSTRINGLENGTH-4)." ...";
        }
        else {
            return $input;
        }

    }

    private final function CacheFileAvailable() {
        return file_exists($this->getCacheFilename());
    }

    /**
     * Grab the feed from its remote location
     * Since PHP still has rather poor threading support we do accept simply accept this call to take a while rather
     * than to fork it in the background and return the cache contents for the meantime
     *
     * @param $cachefallback boolean defines if a fallback to the cache file is allowed if remote resource is unavailable
     * @return null
     */
    private final function GrabFromRemote($cachefallback) {
        try {
            $this->init();
        }
        catch (\Exception $e) {
            //Resource is unavailable
            if ($cachefallback) {
                $this->ReadFromCache();
            }
            else {
                $this->posts = array();
            }
            return NULL;
        }
        $this->processItems();
        $this->WriteToCache();
    }

    /**
     * Reads in $this->posts from the cache file
     */
    private final function ReadFromCache() {
        //TODO
        $this->posts = unserialize(file_get_contents($this->getCacheFilename()));
        return NULL;
    }

    /**
     * Writes $this->posts into the Cache file
     */
    private final function WriteToCache() {
        file_put_contents($this->getCacheFilename(),serialize($this->posts));
    }
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
function writePost($date, $author, $text, $link) {
    $output = array ("timestamp" => $date, "author" => $author, "text" => $text, "link" => $link);
    return $output;
}


final class feedsorter implements newssource {
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
            //TODO uncomment the next line but implement feature to create CACHES on first run
            $feed->SetDownloadAllowed($this->downloadAllowed());
            $postings = $feed->getItems();
            foreach ($postings as $posting) {
                $output[] = $posting;
            }
        }

        //Sort the resulting array with the newest posting first
        foreach ($output as $key => $value) {
            $timestamp[$key] = $value["timestamp"];
        }
        array_multisort($timestamp, SORT_DESC, $output);

        //Emit the n newest postings
        return array_slice($output, 0, $this->itemsToReturn);
    }

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


?>