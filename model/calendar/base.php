<?php

namespace calendar;

require_once dirname(__FILE__).'/../base.php';

final class feedsorter extends \base\feedsorter {
    /**
     * @var bool qualifier is true if the output shall only contain calendar events which are in the future
     */
    private $onlyFutureEvents;

    public function __construct($feeds, $itemsToReturn, $onlyFutureEvents = true) {
        parent::__construct($feeds, $itemsToReturn);
        $this->onlyFutureEvents = $onlyFutureEvents;
    }

    protected function sortItems($items, $itemsToReturn) {
        //Sort the resulting array with the next event (starttime) first
        foreach ($items as $key => $value) {
            $timestamp[$key] = $value["timestamp"];
        }
        array_multisort($timestamp, SORT_ASC, $items);

        if ($this->onlyFutureEvents) {
            //we need to filter the items by date
            $output = array();
            $output_iterator = 0;
            $input_iterator = 0;
            while($output_iterator < $itemsToReturn) {
                if ($input_iterator == count($items)) {
                    //we are out of bounds; no more items available
                    break;
                }
                if ($items[$input_iterator]["timestamp_end"] >= time()) {
                    //the end time of the current item is in the future; append it
                    $output[] = $items[$input_iterator];
                    $output_iterator++;
                }
                $input_iterator++; //lets proceed to the next item
            }
            return $output;
        }
        else {
            //just emit the n newest postings
            return array_slice($items, 0, $itemsToReturn);
        }
    }
}