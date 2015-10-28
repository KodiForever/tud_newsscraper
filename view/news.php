<?php
/**
 * Created by PhpStorm.
 * User: morido
 * Date: 24.02.14
 * Time: 22:10
 */

require_once dirname(__FILE__) . '/../model/news/faculties/faculty_vw.php';
require_once dirname(__FILE__) . '/../model/news/faculties/faculty_ww.php';
require_once dirname(__FILE__)."/../view/formatter.php";

$feedsgatherer = new \output\feedsgatherer();
$feedsgatherer->addFeed(new \news\faculty_vw\Chairs());
$feedsgatherer->addFeed(new \news\faculty_ww\Chairs());
$feeds = $feedsgatherer->getAllFeeds();


$newsformatter = new \output\newsfeed_formatter($feeds, 5);
$newsformatter->generateHTML();