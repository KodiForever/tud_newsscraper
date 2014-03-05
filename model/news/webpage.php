<?php

namespace news;

use base\feedreader;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';
require_once dirname(__FILE__).'/../base.php';


abstract class webpagereader extends feedreader {
    const DOCTYPE = "WEBNEWS";

    protected abstract function convertDate($dateraw);

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
        $unix_timestamp = 0; //defaults to 1970...

        if ((preg_match('/^Stand:\s+(.*)\s/', $trimmed_dateraw, $extracted_date)) === 1) {
            $formatted_dateraw = strptime($extracted_date[1], "%d.%m.%Y %H:%M");
            $unix_timestamp = mktime($formatted_dateraw['tm_hour'], $formatted_dateraw['tm_min'], 0, $formatted_dateraw['tm_mon']+1, $formatted_dateraw['tm_mday'], $formatted_dateraw['tm_year']+1900);
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
        if ((preg_match('/Autor:\s+(.*)$/', $trimmed_authorraw, $extracted_author)) === 1) {
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
        $this->SetPostingsToEmpty();

        //process the actual data
        $items = htmlqp($this->GetRequestData(), '#newslist_box', $this->overrideEncoding())->find('.newslist-linkedtext')->children('a');
        foreach ($items as $item) {
            $link = $item->attr('href');
            $text = $this->tidyText($this->prependText($item->text()));
            if (($subpage = $this->GrabFromRemoteUnconditional($link)) == true) {
                $subpagedata = htmlqp($subpage, '.documentBottomLine')->children('.documentByLine');
                $author = $this->getAuthor($subpagedata->text());
                $date = $this->convertDate($subpagedata->text());

                $this->AppendToPostings($date, $author, $text, $link);
            }
            else {
                //the current subpage is unavailable; skip it
                continue;
            }
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
        $this->SetPostingsToEmpty();

        $entirepage = htmlqp($this->GetRequestData());
        $metadata = $entirepage->find('.documentBottomLine')->children('.documentByLine');
        $author = $this->getAuthor($metadata->text());
        $date = $this->convertDate($metadata->text());
        $link = $this->source;

        $items = $entirepage->find('h1.documentFirstHeading');
        foreach ($items as $item) {
            $text = $this->tidyText($this->prependText($item->text()));

            $this->AppendToPostings($date, $author, $text, $link);
        }
    }
}
