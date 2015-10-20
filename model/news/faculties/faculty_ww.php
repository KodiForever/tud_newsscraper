<?php

namespace news\faculty_ww;

use base\chairreturner;
use news\unstructured_with_heading;
use news\webcmsreader;

require_once dirname(__FILE__).'/../webpage.php';

class Chairs extends chairreturner {

    public function __construct() {
        $this->chairs[] = new webcmsreader("Lasch", "wwlasch", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwllog', true);
        $this->chairs[] = new webcmsreader("Watzka", "wwwatzka", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/wwgkw', true);
        $this->chairs[] = new webcmsreader("EGünther", "wweguenther", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/bu/index_html", true);
        $this->chairs[] = new webcmsreader("ThGünther", "wwthguenther", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/brw", true);
        $this->chairs[] = new webcmsreader("Siems", "wwsiems", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/marketing/index_html", true);
        $this->chairs[] = new webcmsreader("Schirmer", "wwschirmer", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/org", true);
        $this->chairs[] = new unstructured_with_heading("Dobler", "wwdobler", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/wus", "Aktuelles", true);
        $this->chairs[] = new unstructured_with_heading("Thum", "wwthum", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/vwl/fiwi/", "Aktuelles", true);
        $this->chairs[] = new unstructured_with_heading("Leh-Waf", "wwlehwaf", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/vwl/me/index_html", "Aktuelles", true);
        $this->chairs[] = new webcmsreader("Kemnitz", "wwkemnitz", "http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/vwl/wuw/team/kemnitz/document_view", true);
    }
}