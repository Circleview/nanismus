<?php

// Check if this feature is toogled true - The feature toogle is received from the index page

if ($showTwitterTimeline){

    echo "<tr>";
        echo "<td class = 'twitterTableData'>";

// How to embedd twitter feeds https://dev.twitter.com/web/embedded-timelines

    echo "<a class='twitter-timeline' data-lang='de' data-width='270' data-height='225' data-theme='dark' data-link-color='#82c837' href='https://twitter.com/NanismusKW'>Nani auf Twitter</a> <script async src='//platform.twitter.com/widgets.js' charset='utf-8'></script>";

        echo "</td>";
    echo "</tr>";
}

?>