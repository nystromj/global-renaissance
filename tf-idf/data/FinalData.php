<?php

set_time_limit(0);
ini_set('memory_limit','512M');

$files = array("China.txt", "England.txt", "India.txt", "America.txt", "Japan.txt", "France.txt", "Spain.txt", "Portugal.txt", "Egypt.txt", "Korea.txt", "Turk.txt", "Moor.txt", "Virginia.txt");

$categories = array("religion", "politics", "geography", "economics", "race", "proper nouns", "measurements", "verbs", "culture");

// SETTING UP MULTIPLE CATEGORIES

$years = array(1500, 1525, 1550, 1575, 1600, 1625, 1650, 1675, 1700, 1725);
$pattern = "/\d\d\d\d/";

function getBucket ($year, $ranges) {
  foreach($ranges as $i=>$range) {
    if ($year >= $range) {
      if ((($next = next($ranges)) !== NULL ) && $year < $next) {
        return $range;
      }
    }
  }
}

try {
	foreach ($files as $file) {
		$results = array();
	  $all_data = json_decode(file_get_contents("dataResults/topWords/$file"));
	  $yearData = get_object_vars(json_decode(file_get_contents("dataResults/newYears/$file")));
	
		
		foreach($years as $year) {
			foreach($categories as $category) {
				$results[$category]['years'][$year] = 0;
			}
		}
		// add word to appropriate 

	  // create results array, initializing each word
	  foreach ($all_data as $word) {
		  foreach ($word[4] as $cat) {
			$results[$cat]['score'] += floatval($word[3]);
			$results[$cat]['list'][$word[0]] = $word[3];
			//print_r($yearData[$word[0]]);
			foreach($yearData[$word[0]] as $year) {
				preg_match($pattern, $year, $matches);
				$bucket = getBucket($matches[0], $years);
				$results[$cat]['years'][$bucket] += 1;
				$results[$cat]['years']['total'] += 1;
			}
	  	}
	  }
		
		$new_json = str_replace(".txt", ".json", $file);
		file_put_contents("dataResults/finalData/$new_json", json_encode($results));
		

	}
}
catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), " ";
}
?>
