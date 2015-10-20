<?php

namespace news;

require_once dirname(__FILE__).'/../base.php';

final class feedsorter extends \base\feedsorter {

    protected function sortItems($items, $itemsToReturn) {
        //Sort the resulting array with the newest posting first
        foreach ($items as $key => $value) {
            $timestamp[$key] = $value["timestamp"];
        }
        array_multisort($timestamp, SORT_DESC, $items);

        //omit all items in the future
        //we have to loop again, since we need the items to be in order before this makes sense
        $future_offset = 0;
        foreach ($items as $item) {
            if ($item["timestamp"] > time()) {
                $future_offset++;
            }
        }

        //Emit the n newest postings except for future ones
        return array_slice($items, $future_offset, $itemsToReturn+$future_offset);
    }
}