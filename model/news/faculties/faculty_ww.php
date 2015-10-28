<?php

namespace news\faculty_ww;

use base\chairreturner;
use news\webcmsreader;

require_once dirname(__FILE__).'/../webpage.php';

class Chairs extends chairreturner {

    public function __construct() {
        $this->chairs[] = new webcmsreader("Buscher", "wwbuscher", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/lim/index_html');
        $this->chairs[] = new webcmsreader("Lasch", "wwlasch", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwllog');
        $this->chairs[] = new webcmsreader("Karmann", "wwkarmann", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/wwgkw/news/tut_makro');
    }
}