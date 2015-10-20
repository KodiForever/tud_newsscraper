<?php

namespace calendar;

use base\feedreader;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';
require_once dirname(__FILE__).'/../base.php';

abstract class webpagereader extends feedreader {
 //the override in here is not correct. how come?
    const DOCTYPE = "WEBCALENDAR";

    /**
     * Writes a posting to the output-array.
     * Thus ensuring consistent key/value-relations for a single posting in a newssource.
     *
     * @param $date array an array consisting of both the beginning and end of the event
     * @param $author string The author of the posting
     * @param $text string The heading of the posting
     * @param $link string An URL to the posting
     * @return array The formatted posting
     */
    protected function AppendToPostings($date, $author, $text, $link) {
        $output = array ("timestamp" => $date[0], "timestamp_end" => $date[1], "author" => $author, "text" => $text, "link" => $link);
        $this->WritePostingRaw($output);
    }

}

class webcmsreader extends webpagereader {

    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->SetPostingsToEmpty();

        //process the actual data
        $items = htmlqp($this->GetRequestData(), '#tudevent_box', $this->overrideEncoding())->find('.portletContent');
        foreach ($items as $item) {
            if ($item->children('.tudeventlist-eventdate')->count() == 1 and $item->children('.tudeventlist-linkedtext')->count() == 1) {
                $date = $item->children('.tudeventlist-eventdate')->text();
                if (($date = $this->getDates($date)) === false) {
                    //some strange event without any date given; skip it
                    continue;
                }

                $link = $item->children('.tudeventlist-linkedtext')->children('a')->attr('href');
                $text = $item->children('.tudeventlist-linkedtext')->text();
                $text = $this->tidyText($this->prependText($text));

                //TODO feature enhancement: we could determine the author via subsequent calls to the linked calendar entries.
                //but since we are not printing the author anyways, this is currently not implemented
                $author = "n/a";
                $this->AppendToPostings($date, $author, $text, $link);
            }
        }
    }

    /**
     * This returns the beginning and end of an event as a 2-element array or false if $dateraw contains no valid date
     * @param $dateraw
     * @return mixed
     */
    private function getDates($dateraw) {
        $dateraw = trim(preg_replace('/\s+/', ' ', $dateraw)); //remove newlines

        //separate beginning and end of the event
        $dateelements = explode(" - ", $dateraw, 2);
        $output = array();

        //set the beginning of the event
        $output[0] = $this->convertDate($dateelements[0]);

        //check if date is present; fail otherwise
        if ($output[0] === false) {
            return false;
        }

        //set the end of the event
        switch (count($dateelements)) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 2:
                //we have both beginning and end
                $enddate = $this->convertDate($dateelements[1], $dateelements[0]);
                if ($enddate > $output[0]) {
                    //end needs to be after the beginning
                    $output[1] = $enddate;
                    break;
                }
                //date malformed, fallthrough to case 1
            case 1:
                //we only have a beginning and no end
                $output[1] = $output[0]; //force end to the same time as beginning
                break;
            default:
                null; //this cannot happen
        }
        return $output;
    }

    /**
     * This is a generic (begin and end date) function to obtain a valid date from an input string
     * @param string $dateraw the date to be converted
     * @param string $begindate can contain the beginning date if the end is given relative to that date
     * @return mixed (int on success; false on error)
     */
    private function convertDate($dateraw, $begindate = -1) {
        $formatted_dateraw = false;

        //case 1: we have a fully qualified date including hour and minute
        if ((preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4} [0-9]{2}:[0-9]{2}/',$dateraw,$dateraw_extracted)) === 1) {
            $formatted_dateraw = strptime($dateraw_extracted[0], '%d.%m.%Y %H:%M');
        }
        //case2: there is a date but no minute and hour
        elseif ((preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}/',$dateraw,$dateraw_extracted)) === 1) {
            $formatted_dateraw = strptime($dateraw_extracted[0], '%d.%m.%Y');
        }
        //case3: we only have a time (and need to take the date from the $begindate)
        elseif ($begindate != -1 and (preg_match('/^[0-9]{2}:[0-9]{2}/',$dateraw,$dateraw_extracted)) === 1) {
            if ((preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}/',$begindate,$dateonly)) === 1) {
                $fully_qualified_date = $dateonly[0].' '.$dateraw_extracted[0]; //turn enddate into fully qualified date
                $formatted_dateraw = strptime($fully_qualified_date, '%d.%m.%Y %H:%M');
            }
        }
        //return the new unix_timestamp if strptime didnt yield an error
        if ($formatted_dateraw != false) {
            $unix_timestamp = mktime($formatted_dateraw['tm_hour'], $formatted_dateraw['tm_min'], 0, $formatted_dateraw['tm_mon']+1, $formatted_dateraw['tm_mday'], $formatted_dateraw['tm_year']+1900);
            return $unix_timestamp;
        }
        else {
            return false;
        }
    }
}




?>