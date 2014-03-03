<?php
/**
 * Created by PhpStorm.
 * User: morido
 * Date: 11.02.14
 * Time: 22:44
 */

namespace faculty_vw;
require_once dirname(__FILE__).'/../webpage.php';

final class lst_schlag extends \webpagereader {

    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->SetPostingsToEmpty();

        $items = htmlqp($this->GetRequestData(), 'table table tr[valign="top"]');
        foreach ($items as $item) {
            $date = $item->children('td:first')->text();
            $date = $this->convertDate($date);
            // stop if no date is given
            if ($date == false) {
                break;
            }
            $text = $item->children('td:last')->text();
            $text = $this->tidyText($this->prependText($text));
            $author = "n/a";
            $link = $this->source;

            $this->AppendToPostings($date, $author, $text, $link);
        }
    }

    protected function convertDate($dateraw) {
        $formatted_dateraw = strptime($dateraw, "%d.%m.%Y");
        if ($formatted_dateraw == false) {
            return false;
        }
        $unix_timestamp = mktime(0, 0, 0, $formatted_dateraw['tm_mon']+1, $formatted_dateraw['tm_mday'], $formatted_dateraw['tm_year']+1900);
        return $unix_timestamp;
    }
}

final class lst_fricke extends \webpagereader {

    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->SetPostingsToEmpty();

        $author = "n/a";
        $link = $this->source;

        $options = array(
            'convert_from_encoding' => 'iso-8859-1',
        );
        $items = htmlqp($this->GetRequestData(), '.documentContent', $options)->find('h1.documentFirstHeading');
        $items = $items->find('h1.documentFirstHeading'); //we need the second heading

        $items = $items->nextAll('h2');
        foreach ($items as $item) {
            $text = $item->text();
            $text = $this->tidyText($this->prependText($text));
            $date = $item->next('div')->text();
            if (!$date) {
                //posts without a date are skipped
                continue;
            }
            $date = $this->convertDate($date);

            $this->AppendToPostings($date, $author, $text, $link);
        }
    }

    protected function convertDate($dateraw) {
        $formatted_dateraw = strptime($dateraw, "%d-%m-%Y");
        if ($formatted_dateraw == false) {
            return false;
        }
        $unix_timestamp = mktime(0, 0, 0, $formatted_dateraw['tm_mon']+1, $formatted_dateraw['tm_mday'], $formatted_dateraw['tm_year']+1900);
        return $unix_timestamp;
    }
}

//currently unused since it can flood the output with postings of the same date (which is somewhat unfair)
final class lst_ludwig extends \webcmsreader {

    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->SetPostingsToEmpty();

        $entirepage = htmlqp($this->GetRequestData());
        $metadata = $entirepage->find('.documentBottomLine')->children('.documentByLine');
        $author = $this->getAuthor($metadata->text());
        $date = $this->convertDate($metadata->text());
        $link = $this->source;

        $items = $entirepage->find('.documentContent')->find('div#bodyContent.plain')->children('h2');
        foreach ($items as $item) {
            if ($this->isPseudoInfo($item->text())) {
                // skip pseudo "information"
                continue;
            }
            $text = $this->tidyText($this->prependText($item->text()));

            $this->AppendToPostings($date, $author, $text, $link);
        }
    }

    /**
     * Returns true if the current item appears to hold no valuable information (i.e. is used as a spacer on that page)
     *
     * @param $text string The item header under consideration
     * @return bool
     */
    private function isPseudoInfo($text) {
        if ((preg_match('/[A-Za-z]+/', $text)) === 1) {
            return false;
        }
        else {
            //title is "empty" or preg_match returned an error
            return true;
        }
    }
}


$fakultaet = new \webcmsreader("Fakultät", "vwfakultaet", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw');
$lst_becker = new \webcmsreader("Becker", "vwbecker", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/oeko');
$lst_lippold = new \webcmsreader("Lippold", "vwlippold", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/gsa/');
$lst_maier = new \webcmsreader("Maier", "vwmaier", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/svt/index_html');
$lst_schiller = new \webcmsreader("Schiller", "vwschiller", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/tvp/index_html');
$lst_schlag = new lst_schlag("Schlag", "vwschlag", 'http://vplno2.vkw.tu-dresden.de/psycho/content/home/d_news.html');
$lst_stephan = new \webcmsreader("Stephan", "vwlststephan", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibb/eb');
$lst_fengler = new \webcmsreader("Fengler", "vwfengler", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibv/gvb/index_html');
$lst_stopka = new \webcmsreader("Stopka", "vwstopka", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/kom/');
$lst_freyer = new \webcmsreader("Freyer", "vwfreyer", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/tou/index_html');
$lst_fricke = new lst_fricke("Fricke", "vwfricke", "http://www.ifl.tu-dresden.de/?dir=Professur/Aktuelles");
$lst_ludwig = new \unstructured_with_heading("Ludwig", "vwludwig", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/vlo/studium/aktuelles");
$lst_wieland = new \unstructured_with_heading("Wieland", "vwwieland", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/vwipol/Aktuelles");
$lst_laemmer = new \unstructured_with_heading("Lämmer", "vwlaemmer", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/vos/news/index_html");
$lst_nachtigall = new \webcmsreader("Nachtigall", "vwnachtigall", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ila/vkstrl");