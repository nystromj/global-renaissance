<?php

set_time_limit(0);
ini_set('memory_limit','2048M');

$files = array( "shakespeare.json", "milton.json");



try {
  foreach ($files as $f) {
    $test = "dataResults/authorPaths/$f";
    $name = str_replace('.json', '', $f);

    $contents = json_decode(file_get_contents($test));

    // init results string
    $all_relevant_text = "";

    // load each json file and concat all contents together
    foreach ($contents as $con) {
      foreach ($con as $i=>$u) {
        if ($i == 'file') {
          $filepath = "Global Renaissance/raw/$u";
          $text = json_decode(file_get_contents($filepath));
          // now we go through each paragraph in each section in each file
          foreach ($text as $i=>$sec) {
            foreach ($sec as $type=>$s) {
              foreach ($s as $m=>$para) {
                $all_relevant_text .= $para . ' ';
              }
            }
          }
        }
      }
    } // foreach

    // dump contents into literature directory
    file_put_contents( "dataResults/literature/$name.txt", $all_relevant_text);
    echo "Done with $name.<br/>";

  }
}
catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), " ";
}
?>