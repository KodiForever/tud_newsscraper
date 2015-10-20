<?php
/**
 * Created by PhpStorm.
 * User: morido
 * Date: 11.02.14
 * Time: 22:44
 */

namespace news\faculty_vw;
use base\chairreturner;
use news\unstructured_with_heading;
use news\webcmsreader;
use news\webpagereader;

require_once dirname(__FILE__).'/../webpage.php';

final class lst_fricke extends webpagereader {

    protected function processItems() {
        //ensure that we are not appending to old data (i.e. if this method is called more than once)
        $this->SetPostingsToEmpty();

        $author = "n/a";
        $link = $this->source;

        $items = htmlqp($this->GetRequestData(), '.documentContent', $this->overrideEncoding())->find('h1.documentFirstHeading');
        $items = $items->find('h1.documentFirstHeading'); //we need the second heading

        $items = $items->nextAll('h2');
        foreach ($items as $item) {
            $date = $item->next('div')->text();
            $date = $this->convertDate($date, "%d-%m-%Y");
            if ($date === false) {
                //posts without a date are skipped
                continue;
            }
            $text = $item->text();
            $text = $this->tidyText($this->prependText($text));

            $this->AppendToPostings($date, $author, $text, $link);
        }
    }
}

//currently unused since it can flood the output with postings of the same date (which is somewhat unfair)
final class lst_ludwig extends webcmsreader {

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

class Chairs extends chairreturner {

    public function __construct() {
        $this->chairs[] = new webcmsreader("Fakultät", "vwfakultaet", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw', true);
        $this->chairs[] = new webcmsreader("Becker", "vwbecker", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/oeko', true);
        $this->chairs[] = new webcmsreader("Lippold", "vwlippold", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/gsa/', true);
        $this->chairs[] = new webcmsreader("Maier", "vwmaier", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/svt/index_html', true);
        $this->chairs[] = new webcmsreader("Schiller", "vwschiller", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/tvp/index_html', true);
        $this->chairs[] = new webcmsreader("Schlag", "vwschlag", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/vpsy');
        $this->chairs[] = new webcmsreader("Stephan", "vwstephan", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibb/eb');
        $this->chairs[] = new webcmsreader("Fengler", "vwfengler", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibv/gvb/index_html', true);
        $this->chairs[] = new webcmsreader("Stopka", "vwstopka", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/kom/aktuelles');
        $this->chairs[] = new webcmsreader("Freyer", "vwfreyer", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/tou/index_html', true);
        $this->chairs[] = new lst_fricke("Fricke", "vwfricke", "http://www.ifl.tu-dresden.de/?dir=Professur/Aktuelles");
        $this->chairs[] = new unstructured_with_heading("Ludwig", "vwludwig", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/vlo/studium/aktuelles");
        $this->chairs[] = new unstructured_with_heading("Wieland", "vwwieland", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/vwipol/Aktuelles");
        $this->chairs[] = new unstructured_with_heading("Lämmer", "vwlaemmer", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/vos/news/index_html");
        $this->chairs[] = new webcmsreader("Nachtigall", "vwnachtigall", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ila/vkstrl", true);
        $this->chairs[] = new webcmsreader("Krimmling", "vwkrimmling", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/vis/vlp", true);
	$this->chairs[] = new webcmsreader("Schütte", "vwschuette", "http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibv/vsys", true);
    }
}
