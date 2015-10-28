<?php

require_once dirname(__FILE__).'/../vendor/autoload.php';
require_once 'base.php';


/**
 * Class rssreader
 *
 * A class to read out RSS feeds
 */
abstract class rssreader extends base\feedreader {

    /**
     * @var string $source holds the url of the feed to be processed
     * @var [] $feedhandler the handler to the current instance of SimplePie
     */
    private $source = '';
    protected $feedhandler;
    const DOCTYPE = "RSS";

    /**
     * @param string $source url of the feed to be processed
     * @param string $feedid the unique id of the current string
     */
    protected  function __construct($source, $feedid) {
        $this->feedid = $feedid;
        $this->source = $source;
    }

}


abstract class foodreader extends rssreader {

    protected function processItems() {

        // we assume normal RSS-feeds here
        // i.e. we need: posting-text, author, time, link

        // read out price, description
        for ($i=0; $i < $this->feedhandler->get_item_quantity(); $i++)
        {
            $item = $this->feedhandler->get_item($i);
            $title = $item->get_title();

        }



        $this->processFoodItems();


        return NULL;
    }

    protected function processFoodItems() {
        //only a stub here
    }

}


?>