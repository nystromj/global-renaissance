<?php

set_time_limit(0);
ini_set('memory_limit','1024M');

// point to results of hadoop. change file for each file in directory (e.g., part-r-00001)
$hadoop_contents = file_get_contents('dataResults/hadoopResults/part-r-00000');
foreach(preg_split("/((\r?\n)|(\r\n?))/", $hadoop_contents) as $line){
  //echo "$line<br>";
  $c = explode('@', $line);
  $word = $c[0];
  $cn = explode('.txt', $c[1]);
  $source = $cn[0];

  $da = explode('[', $c[1]);
  $d = str_replace(']', '', $da[1]);

  $output = str_replace(' ', '', "$word, $d\r\n");


  // use dataResults/authors directory for parsing milton and shakespeare results
  // use dataResults/commaSep directory for parsing country results
  file_put_contents("dataResults/authors/$source.txt", $output, FILE_APPEND);
}


echo "Done";
?>