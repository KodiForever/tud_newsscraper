<?php

namespace calendar\faculty_vw;
use base\chairreturner;
use calendar\webcmsreader;

require_once dirname(__FILE__).'/../webpage.php';

class Chairs extends chairreturner {

    public function __construct() {
        $this->chairs[] = new webcmsreader("Fakultät", "vwfakultaet", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/');
        $this->chairs[] = new webcmsreader("Trinckauf", "vwtrinckauf", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibv/vst/index_html');
        $this->chairs[] = new webcmsreader("Nachtigall", "vwnachtigall", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ila/vkstrl');
        $this->chairs[] = new webcmsreader("Fengler", "vwfengler", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ibv/gvb/index_html');
        $this->chairs[] = new webcmsreader("Lippold", "vwlippold", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/ivs/gsa/');
        $this->chairs[] = new webcmsreader("Michler", "vwmichler", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/vis/itvs');
        $this->chairs[] = new webcmsreader("Stopka", "vwstopka", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/iwv/kom/');
        $this->chairs[] = new webcmsreader("Krimmling", "vwkrimmling", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/vkw/vis/vlp/index_html');
    }
}

?>