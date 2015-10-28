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

        //Emit the n newest postings
        return array_slice($items, 0, $itemsToReturn);
    }
}