<?php
/**
 * Created by PhpStorm.
 * User: morido
 * Date: 24.02.14
 * Time: 22:10
 */

require_once dirname(__FILE__).'/../model/faculties/faculty_vw.php';
require_once dirname(__FILE__).'/../model/faculties/faculty_ww.php';
require_once dirname(__FILE__)."/../view/formatter.php";

$feeds[] = $fakultaet;
$feeds[] = $lst_becker;
$feeds[] = $lst_lippold;
$feeds[] = $lst_maier;
$feeds[] = $lst_schiller;
$feeds[] = $lst_schlag;
$feeds[] = $lst_stephan;
$feeds[] = $lst_fengler;
$feeds[] = $lst_stopka;
$feeds[] = $lst_freyer;
$feeds[] = $lst_fricke;
$feeds[] = $lst_ludwig;
$feeds[] = $lst_wieland;
$feeds[] = $lst_laemmer;
$feeds[] = $lst_nachtigall;
$feeds[] = $lst_buscher;
$feeds[] = $lst_lasch;
$feeds[] = $lst_karmann;


$newsformatter = new \output\newsfeed_formatter($feeds, 5);
$newsformatter->generateHTML();


?>