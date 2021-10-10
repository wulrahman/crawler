<?php

require_once('common.php');

require_once('search.php');

require_once('main.php');

$sql = "SELECT `id` FROM `domain`";
	
$query = mysqli_query($Lid, $sql);

while ($row = mysqli_fetch_object($query)) {

	$unique[$row->id] = $row->id;

}

$rank_count = pagerank("", "", 1);

foreach($unique as $id => $main) {

	$rank = "";

	foreach($rank_count[$main] as $key => $other) {

		$rank[] = (1/$other);
	
	}

	$domain_rank[$main] = array_sum($rank);

	mysqli_query($Lid, "UPDATE `domain` SET `page_rank` = '".$domain_rank[$main]."' WHERE `domain`.`id` = ".$main.";");

}

?>
