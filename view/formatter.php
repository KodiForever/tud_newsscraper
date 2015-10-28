<?php
/**
 * Created by PhpStorm.
 * User: morido
 * Date: 12.02.14
 * Time: 00:04
 */

namespace output;

use \news\feedsorter as news_feedsorter;
use \calendar\feedsorter as calendar_feedsorter;

require_once dirname(__FILE__) . '/../model/news/base.php';
require_once dirname(__FILE__) . '/../model/calendar/base.php';

interface formatter {
    public function generateHTML();
}

abstract class generic_formatter implements formatter {
    protected $sorter;

    public function generateHTML() {
        $posts = $this->sorter->getItems();

        echo "<ul>\n";
        foreach ($posts as $post) {
            echo "<li>";
            echo "<a target=\"_blank\" href=\"".$post['link']."\">";
            echo htmlentities($post['text'], ENT_COMPAT | 'ENT_HTML5' | ENT_QUOTES, 'UTF-8');
            echo " ";
            echo "<span class=\"datum\">".$this->TimeFormatter($post['timestamp'])."</span>";
            echo "</a>";
            echo "</li>";
            echo "\n";
        }
        echo "</ul>";
    }

    protected abstract function TimeFormatter($absoluteTimestamp);
}


class newsfeed_formatter extends generic_formatter {

    /**
     * @param $feeds array The feeds to be processed
     * @param $itemsToReturn integer The maximum number of items to return
     */
    public function __construct ($feeds, $itemsToReturn) {
        $this->sorter = new news_feedsorter($feeds, $itemsToReturn);
    }

    //TODO
    //posts may have an arbitrary number of columns (title, author, time...)
    //so we need some kind of overloeading to handle this
    //Solution: we will have different classes to handle this

    /**
     * A function to turn an absolute timelength into a relative human-readable (German) one based on the current time
     *
     * @param integer $absoluteTimestamp Absolute input in seconds
     * @return string Relative time as human-readable string in German
     */
    protected function TimeFormatter($absoluteTimestamp) {
        //this requires PHP 5.3

        $now = new \DateTime();
        $othertime = new \DateTime("@".$absoluteTimestamp);
        $interval = $othertime->diff($now);
        $days = $interval->days;

        switch($days) {
            case 0:
                $output = "<24h"; break;
            case 1:
                $output = "<48h"; break;
            default;
                $output = "vor ".$days." Tagen";
        }
        return $output;
    }
}

class calendarfeed_formatter extends generic_formatter {

    public function __construct($feeds, $itemsToReturn) {
        $this->sorter = new calendar_feedsorter($feeds, $itemsToReturn, true);
    }

    protected function TimeFormatter($absoluteTimestamp) {
        if (date("H:i", $absoluteTimestamp) == "00:00") {
            //truncate the time for midnight appointments
            $date = date("d.m.Y", $absoluteTimestamp);
        }
        else {
            $date = date("d.m.Y H:i", $absoluteTimestamp);
        }
        return $date;
    }
}



/**
 * Class feedsgatherer
 *
 * Combines all available feeds from different sources into one big array
 *
 * @package output
 */
class feedsgatherer {
    private $feeds = array();

    public function addFeed($feed) {
        $this->feeds[] = $feed;
    }

    public function getAllFeeds() {
        $output = array();
        foreach ($this->feeds as $feed) {
            $output = array_merge($output, $feed->getAllChairs());
        }
        return $output;
    }
}
