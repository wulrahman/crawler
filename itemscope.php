<?php

require_once("../portable-utf8.php");

require_once('../simple_html_dom.php');

require_once('../setting.php');

require_once('../common.php');

$url = "https://www.youtube.com/?gl=GB&hl=en-GB";

$array = url_info(strtolower(redirection($url)), 1);

$array = site_info($array);

$array = itemscope($array);

unset($array['response']);

print("<pre>".print_r($array,true)."</pre>");


?>