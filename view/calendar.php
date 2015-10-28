<?php

require_once dirname(__FILE__) . '/../model/calendar/faculties/faculty_vw.php';
require_once dirname(__FILE__)."/../view/formatter.php";

$feedsgatherer = new \output\feedsgatherer();
$feedsgatherer->addFeed(new \calendar\faculty_vw\Chairs());
$feeds = $feedsgatherer->getAllFeeds();


$newsformatter = new \output\calendarfeed_formatter($feeds, 5);
$newsformatter->generateHTML();