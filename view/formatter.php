<?php
/**
 * Created by PhpStorm.
 * User: morido
 * Date: 12.02.14
 * Time: 00:04
 */

namespace output;

interface formatter {
    public function generateHTML();
}


class newsfeed_formatter implements formatter {

    private $sorter;

    /**
     * @param $feeds \base\feedreader
     */
    public function __construct ($feeds, $itemstoreturn) {
        $this->sorter = new \base\feedsorter($feeds, $itemstoreturn);

    }

    //TODO
    //posts may have an arbitrary number of columns (title, author, time...)
    //so we need some kind of overloeading to handle this
    //Solution: we will have different classes to handle this

    public function generateHTML() {
        $posts = $this->sorter->getItems();

        echo "<ul>\n";
        foreach ($posts as $post) {
            echo "<li>";
            echo "<a target=\"_blank\" href=\"".$post['link']."\">";
            echo htmlentities($post['text'], ENT_COMPAT | 'ENT_HTML5' | ENT_QUOTES, 'UTF-8');
            echo " ";
            echo "<span class=\"datum\">".relativeTime($post['timestamp'])."</span>";
            echo "</a>";
            echo "</li>";
            echo "\n";
        }
        echo "</ul>";
    }
}

/**
 * A function to turn an absolute timelength into a relative human-readable (German) one based on the current time
 *
 * @param integer $absoluteTimestamp Absolute input in seconds
 * @return string Relative time as human-readable string in German
 */
function relativeTime($absoluteTimestamp) {
    //this requires PHP 5.3

    $now = new \DateTime();
    $othertime = new \DateTime("@".$absoluteTimestamp);
    $interval = $othertime->diff($now);
    $days = $interval->days;

    switch($days) {
        case 0:
            $output = "heute"; break;
        case 1:
            $output = "gestern"; break;
        default;
            $output = "vor ".$days." Tagen";
    }
    return $output;
}