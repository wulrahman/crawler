<?php

ob_start();

require_once("common.php");

require_once("search.php");

require_once("summary/SummaryTool.php");
require_once("summary/SentenceTokenizer.php");

$url = "https://cragglist.com/crawler/crawler.php";

$array = url_info(strtolower(redirection($url)));

header("Refresh: 5;url=".$site_url."/auto.php");

?>
