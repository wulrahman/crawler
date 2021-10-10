<?php

ob_start();

require_once('simple_html_dom.php');

require_once("portable-utf8.php");

require_once("common.php");

require_once("main.php");

$dept = 3;

$domain_time = 'interval 100 day';

$url_time = 'interval 100 day';

$get_domain = mysqli_query($Lid,"SELECT SQL_CALC_FOUND_ROWS `domain`, `id`  FROM `domain` WHERE `timestamp` < now() - ".$domain_time." LIMIT 0, 5");

$count_domain = array_pop(mysqli_fetch_row(mysqli_query($Lid,"SELECT FOUND_ROWS()")));

if ($count_domain > 0) {

	while ($rows = mysqli_fetch_object($get_domain)) {

		mysqli_query($Lid, "UPDATE `domain` SET `timestamp` = Now() WHERE `domain`.`id` = '".$rows->id."';");

		$query = mysqli_query($Lid,"SELECT `id`, `url`, `domain`, `company`, `dept` FROM `url` WHERE `domain` = '".$rows->id."' AND `indexed` < now() - ".$url_time." AND `dept` < ".$dept."");

		while ($row = mysqli_fetch_object($query)) {

			urlLooper(urladder($row), $row);

		}

	}

}
else {

	$query = mysqli_query($Lid,"SELECT `id`, `url`, `domain`, `dept` FROM `url` WHERE `indexed` < now() - ".$url_time." AND `dept` < ".$dept."");

	while ($row = mysqli_fetch_object($query)) {

		urlLooper(urladder($row), $row);

	}

}

mysqli_close($Lid);

header("Refresh: 5;url=".$site_url."/crawler.php");

?>
