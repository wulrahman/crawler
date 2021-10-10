<?php

require_once('common.php');

require_once('search.php');

require_once('main.php');

require_once('stemwords.php');

$query_time = 'interval 5 day';

$query_search = mysqli_query($Lid, "SELECT SQL_CALC_FOUND_ROWS `id`, `query` FROM `query` WHERE `timestamp` < now() - ".$query_time." LIMIT 0, 100");

$count_query = array_pop(mysqli_fetch_row(mysqli_query($Lid,"SELECT FOUND_ROWS()")));

$deep_search = 0;

if ($count_query > 0) {

	$rank_count = pagerank("", "", 1);

	while ($search_row = mysqli_fetch_object($query_search)) {

		mysqli_query("DELETE FROM `result` WHERE `query` = '".$search_row->id."'");

		$map = "";

		$match = "";

		mysqli_query($Lid, "UPDATE `query` SET `timestamp` = Now() WHERE `query`.`id` = '".$search_row->id."';");

		$common = extractCommonWords($search_row->query, 100);

		$expand = array_count_values(str_word_count(strtolower($search_row->query), 1));

		foreach($expand as $key => $matchs) {

			if(array_key_exists($key, $match)) {

				unset($match[$key]);

			}

			$match[] = '+'.$key;

		}

		$matchs = implode(' ', array_unique($match));

		$sql = sql_main($matchs, $deep_search);

		$query = mysqli_query($Lid, $sql);

		while ($row = mysqli_fetch_object($query)) {

			$map[$row->id] = $row->relevance;

			$domain[$row->id] = $row->domain;

		}
	
		$unique = array_unique($domain);

		$total_links = array();

		foreach($unique as $id => $main) {

			$total_links = array_merge_recursive($total_links, $rank_count[$main]);

		}

		foreach($unique as $id => $main) {

			$domain_rank[$main] = pagerank("", "", 2, $main);

			$factor[$main] = ($domain_rank[$main])/$total_links[$main];

		}

		foreach($map as $id => $relevance) {

			$rank = "";

			foreach($rank_count[$domain[$id]] as $key => $main) {

				$rank[] = $factor[$main];

			}

			$array_sum[$id] = array_sum($rank);

			if($array_sum[$id] > 0) {

				$map[$id] = $relevance * $array_sum[$id];

			}
			else {

				$map[$id] = $relevance;

			}

			mysqli_query($Lid, "INSERT INTO `result`(`query`, `order`, `meta`) VALUES ('".$search_row->id."','".$map[$id]."', '".$id."')");
	
		}

	}

}

?>
