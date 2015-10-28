<?php

namespace calendar;

use base\feedreader;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';
require_once dirname(__FILE__).'/../base.php';

abstract class webpagereader extends feedreader {
 //the override in here is not correct. how come?
    const DOCTYPE = "WEBCALENDAR";
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
                $date = $this->convertDate($date);
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

    protected function convertDate($dateraw) {
        //strip off everything but the date from the input
        $dateraw = trim(preg_replace('/\s+/', ' ', $dateraw)); //remove newlines
        $unix_timestamp = 0; //defaults to 1970
        $formatted_dateraw = false;

        //case 1: we have a fully qualified date including hour and minute
        if ((preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4} [0-9]{2}:[0-9]{2}/',$dateraw,$dateraw_extracted)) === 1) {
            $formatted_dateraw = strptime($dateraw_extracted[0], '%d.%m.%Y %H:%M');
        }
        //case2: there is a date but no minute and hour
        elseif ((preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}/',$dateraw,$dateraw_extracted)) === 1) {
            $formatted_dateraw = strptime($dateraw_extracted[0], '%d.%m.%Y');
        }
        //return the new unix_timestamp if strptime didnt yield an error
        if ($formatted_dateraw != false) {
            $unix_timestamp = mktime($formatted_dateraw['tm_hour'], $formatted_dateraw['tm_min'], 0, $formatted_dateraw['tm_mon']+1, $formatted_dateraw['tm_mday'], $formatted_dateraw['tm_year']+1900);
        }

        return $unix_timestamp;
    }
}




?>