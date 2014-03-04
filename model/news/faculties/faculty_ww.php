<?php

namespace news\faculty_ww;

use news\webcmsreader;

require_once dirname(__FILE__).'/../webpage.php';


$lst_buscher = new webcmsreader("Buscher", "wwbuscher", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwl/lim/index_html');
$lst_lasch = new webcmsreader("Lasch", "wwlasch", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/bwllog');
$lst_karmann = new webcmsreader("Karmann", "wwkarmann", 'http://tu-dresden.de/die_tu_dresden/fakultaeten/fakultaet_wirtschaftswissenschaften/wwgkw/news/tut_makro');