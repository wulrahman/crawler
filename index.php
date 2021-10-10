<?php

$before = microtime(true);

require_once("../portable-utf8.php");

require_once("../common.php");

require_once('stemming/wordstemmer.php');
require_once('stemming/search.php');

require_once("summary/SummaryTool.php");
require_once("summary/SentenceTokenizer.php");

$q = $_GET['q'];

$type = $_GET['type'];

$limit = intval($_GET['limit']);

if($_GET['deep_search'] == 1) {

	$deep_search = 1;

}

$page = preg_replace('/[^-0-9]/', '', $_GET['page']);

if($page=="" or $page==" " or $page=="0") {

	$page="1";

}

$q = htmlentities(stripslashes($q), ENT_QUOTES | ENT_IGNORE, "UTF-8");

$q = html_entity_decode($q);

if($q=="") {

}
else if(isset($q)) {

	$q = mysqli($q);

	$query_sql = "SELECT COUNT(`id`) as `count`, `id` FROM `query` WHERE `query` = '".$q."'";

	$query_row = mysqli_fetch_object(mysqli_query($Lid, $query_sql));

	if($query_row->count == 0) {

		mysqli_query($Lid,"INSERT INTO `query` (`query`) VALUES ('".$q."')");

	}

	$common = extractCommonWords($q, 100);

	$common = array_keys($common);

	foreach($common as $p) {

		if(!space($p)) {

			$check[] = $p;

			if(strlen($p) > 1 ) {

				$bold[] = $p;

			}

		}

	}


	$expand = array_count_values(str_word_count(strtolower($q), 1));

	$array = web($expand, $common, $q, $page, $limit, $deep_search, $query_row);

	$count = $array['count'];

	$results['stem'] = $array['stem'];

	unset($array['stem']);

	unset($array['count']);

	if($spell = didyoumean($check)) {

		$results['spell'] = $spell;

	}

	if($page == 1) {

		$main_result = current($array);

		$main_key = key($array);

		foreach($array as $key => $main) {

			if($main_result->company == $main->company && $main_key != $key) {

				$count_link = array_pop(mysqli_fetch_row(mysqli_query($Lid, "SELECT COUNT(`id`) FROM `map` WHERE `domain` = '".$main_result->company."' AND `found_domain` = '".$main_result->company."' AND `url` = '".$main->old_url."'")));

				if($count_link > 0) {

					$rank[$key] = $count_link;

				}

			}

		}

	}

	$medium = array_sum($rank)/COUNT($rank);

	foreach ($array as $key => $main) {

		if (array_key_exists($key, $rank) && ($rank[$key] >= $medium)) {

			$results['results'][0]['main'][] = array('url' => urlencode($main->url), 'title' => urlencode(make_bold(limiter(urldecode($main->title), 10, array('�')) , $bold)), 'showurl' => urlencode(show_url($main->url)), 'abstract' => urlencode(make_bold(limiter(urldecode($main->abstract), 30, array('�')), $bold)));

		}
		else {

			$results['results'][] = array('url' => urlencode($main->url), 'title' => urlencode(make_bold(limiter(urldecode($main->title), 10, array('�')) , $bold)), 'showurl' => urlencode(show_url($main->url)), 'abstract' => urlencode(make_bold(limiter(urldecode($main->abstract), 30, array('�')), $bold)));

		}

  }

	$json = json_encode($results);

	print_r($json);

}

$after = microtime(true);
//echo ($after-$before) . " sec/serialize\n";

?>
