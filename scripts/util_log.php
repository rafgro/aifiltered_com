<?php

function write_log($txt) {
    file_put_contents('./log.txt', $txt."\n", FILE_APPEND);
}
