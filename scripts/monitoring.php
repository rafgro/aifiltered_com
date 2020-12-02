<?php

$log = array_reverse(explode("\n",file_get_contents('./log.txt')));
$logtxt = implode("<br/>",$log);
echo($logtxt);