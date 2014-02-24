<?php

require_once dirname(__FILE__).'/../vendor/autoload.php';
require_once 'base.php';


abstract class webpagereader extends base\feedreader {

    protected $source = "";
    protected $webpage = "";
    /**
     * @var string $publicname is a string to prepend the title of each newsentry with
     */
    protected $publicname = "";
    const DOCTYPE = "WEB";


    protected final function init() {
        $this->webpage = file_get_contents($this->source);
        if ($this->webpage == false) {
            throw new \Exception("Web-Resource not available");
        }
    }

    protected function prependText($text) {
        if (empty($this->publicname)) {
            return trim($text);
        }
        else {
            return "[".$this->publicname."] ".trim($text);
        }
    }

    protected abstract function convertDate($dateraw);

    public function __construct($publicname, $feedid, $source) {
        $this->source = $source;
        $this->publicname = $publicname;
        $this->feedid = $feedid;
    }

}

class webcmsreader extends webpagereader {

    /**
     * Converts date + time as given in the scraped HTML into a unix timestamp
     *
     * @param string $dateraw
     * @return int
     */
    protected function convertDate($dateraw) {
        $trimmed_dateraw = trim($dateraw);
        $regex_match = preg_match('/^Stand:\s+(.*)\s/', $trimmed_dateraw, $extracted_date);
        if ($regex_match === 1) {
            $formatted_dateraw = strptime($extracted_date[1], "%d.%m.%Y %H:%M");
            $unix_timestamp = mktime($formatted_dateraw['tm_hour'], $formatted_dateraw['tm_min'], 0, $formatted_dateraw['tm_mon']+1, $formatted_dateraw['tm_mday'], $formatted_dateraw['tm_year']+1900);

        }
        else {
            //an error occurred
            $unix_timestamp = 0; //welcome to 1970...
        }
        return $unix_timestamp;
    }

    /**
     * Returns the author of the currently processed posting
     *
     * This used to be a simple "$author = $subpagedata->children('a')->text();" in $this->processItems().
     * However, some authors don't come embraced by a link...
     *
     * @param string $authorraw
     * @return string
     */
    protected function getAuthor($authorraw) {
        $trimmed_authorraw = trim($authorraw);
        $regex_match = preg_match('/Autor:\s+(.*)$/', $trimmed_authorraw, $extracted_author);
        if ($regex_match === 1) {
            $author = $extracted_author[1];
        }
        else {
            //an error occurred
            $author = "n/a";
        }
        return $author;
    }


    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->posts = array();

        $items = htmlqp($this->webpage, '.portletBody')->find('.newslist-linkedtext')->children('a');
        foreach ($items as $item) {
            $link = $item->attr('href');
            $text = $this->tidyText($this->prependText($item->text()));
            $subpage = file_get_contents($link);
            $subpagedata = htmlqp($subpage, '.documentBottomLine')->children('.documentByLine');
            $author = $this->getAuthor($subpagedata->text());
            $date = $this->convertDate($subpagedata->text());

            $output = \base\writePost($date, $author, $text, $link);
            $this->posts[] = $output;
        }
    }

}


/**
 * Class unstructured_with_heading
 *
 * Generates only a single news posting with the contents of h1 as the title
 * intended for merely unparsable (i.e. unstructured) junk
 */
final class unstructured_with_heading extends webcmsreader {

    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->posts = array();

        $entirepage = htmlqp($this->webpage);
        $metadata = $entirepage->find('.documentBottomLine')->children('.documentByLine');
        $author = $this->getAuthor($metadata->text());
        $date = $this->convertDate($metadata->text());
        $link = $this->source;

        $items = $entirepage->find('h1.documentFirstHeading');
        foreach ($items as $item) {
            $text = $item->text();
            $text = $this->tidyText($this->prependText($item->text()));

            //$output = array();
            $output = \base\writePost($date, $author, $text, $link);
            //array_push($output, $date, $author, $text, $link);
            $this->posts[] = $output;
        }
    }
}

