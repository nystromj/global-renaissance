<?php

set_time_limit(0);
ini_set('memory_limit','512M');

include_once( 'continents.php' );

$continents = array($south_america, $oceania, $north_america, $europe, $asia, $africa, $antartica);
$authors = array("shakespeare.txt", "milton.txt");


/* getContinentsDensity takes an array of countries and an array of words and returns an array with
 * the density of each word for the given set of countries
 */
function getContinentDensity($continent_array, $queries) {

  $results = array("total_words" => 0);
  foreach($queries as $query) {
    $results[$query] = 0;
  }

  foreach ($continent_array as $country) {
    $all_data = json_decode(file_get_contents("dataResults/sorted/$country"));

    // check if empty file
    if (count($all_data) == 0) {
      continue;
    }

    // add to total words
    $results["total_words"] += explode("/", $all_data[0][2])[1];

    // search for word and add to occurences if found
    foreach ($queries as $query) {

      foreach($all_data as $word) {
        if ($word[0] == $query) {
          $results[$query] += explode("/", $word[2])[0];
          break;
        }
      }

    }
  }

  return $results;
}

/* getAuthorDensity takes the tf-idf results file for an author and a list of words and returns an array
 * with the density of each word for the given author's corpus.
 */
function getAuthorDensity ($author, $queries) {
  $results = array();
  foreach($queries as $query) {
    $results[$query] = 0;
  }
  $author_data = file_get_contents("dataResults/authors/$author");
  $author_array = explode("\n", $author_data);
  echo $info[0];
  foreach ($queries as $query) {
    foreach ($author_array as $word) {
      $info = explode(",", $word);
      if($info[0] == $query) {
        $results[$query] = $info[2];
        break;
      }
    }
  }
  return $results;
}

$files = array("Afghanistan.txt", "Albania.txt", "Algeria.txt", "America.txt", "Angola.txt", "Antarctica.txt", "Argentina.txt", "Armenia.txt", "Australia.txt", "Austria.txt", "Azerbaijan.txt", "Bahamas.txt", "Bangladesh.txt", "Belarus.txt", "Belgium.txt", "Belize.txt", "Benin.txt", "Bhutan.txt", "Bolivia.txt", "Bosnia and Herz..txt", "Botswana.txt", "Brazil.txt", "Brunei.txt", "Bulgaria.txt", "Burkina Faso.txt", "Burundi.txt", "Cambodia.txt", "Cameroon.txt", "Canada.txt", "Central African Rep..txt", "Chad.txt", "Chile.txt", "China.txt", "Colombia.txt", "Congo.txt", "Costa Rica.txt", "CoÌ‚te d'Ivoire.txt", "Croatia.txt", "Cuba.txt", "Cyprus.txt", "Czech Rep..txt", "Dem. Rep. Congo.txt", "Dem. Rep. Korea.txt", "Denmark.txt", "Djibouti.txt", "Dominican Rep..txt", "Ecuador.txt", "Egypt.txt", "El Salvador.txt", "England.txt", "Eq. Guinea.txt", "Eritrea.txt", "Estonia.txt", "Ethiopia.txt", "Falkland Is..txt", "Fiji.txt", "Finland.txt", "Fr. S. Antarctic Lands.txt", "France.txt", "Gabon.txt", "Gambia.txt", "Georgia.txt", "Germany.txt","Ghana.txt", "Greece.txt", "Greenland.txt", "Guatemala.txt", "Guinea-Bissau.txt", "Guinea.txt", "Guyana.txt", "Haiti.txt", "Honduras.txt", "Hungary.txt", "Iceland.txt", "India.txt", "Indonesia.txt", "Iran.txt", "Iraq.txt", "Ireland.txt", "Israel.txt", "Italy.txt", "Jamaica.txt", "Japan.txt", "Jordan.txt", "Kazakhstan.txt", "Kenya.txt", "Korea.txt", "Kosovo.txt", "Kuwait.txt", "Kyrgyzstan.txt", "Lao PDR.txt", "Latvia.txt", "Lebanon.txt", "Lesotho.txt", "Liberia.txt", "Libya.txt", "Lithuania.txt", "Luxembourg.txt", "Macedonia.txt", "Madagascar.txt", "Malawi.txt", "Malaysia.txt", "Mali.txt", "Mauritania.txt", "Mexico.txt",  "Moldova.txt", "Mongolia.txt", "Montenegro.txt", "Morocco.txt", "Mozambique.txt", "Myanmar.txt", "N. Cyprus.txt", "Namibia.txt", "Nepal.txt", "Netherlands.txt", "New Caledonia.txt", "New Zealand.txt", "Nicaragua.txt", "Niger.txt", "Nigeria.txt", "Norway.txt", "Oman.txt", "Pakistan.txt", "Palestine.txt", "Panama.txt", "Papua New Guinea.txt", "Paraguay.txt", "Peru.txt", "Philippines.txt", "Poland.txt", "Portugal.txt", "Puerto Rico.txt", "Qatar.txt", "Romania.txt", "Russia.txt", "Rwanda.txt", "S. Sudan.txt", "Saudi Arabia.txt", "Senegal.txt", "Serbia.txt", "Sierra Leone.txt", "Slovakia.txt", "Slovenia.txt", "Solomon Is..txt", "Somalia.txt", "Somaliland.txt", "South Africa.txt", "Spain.txt", "Sri Lanka.txt", "Sudan.txt", "Suriname.txt", "Swaziland.txt", "Sweden.txt", "Switzerland.txt", "Syria.txt", "Taiwan.txt", "Tajikistan.txt", "Tanzania.txt", "Thailand.txt", "Timor-Leste.txt", "Togo.txt", "Trinidad and Tobago.txt", "Tunisia.txt", "Turkey.txt", "Turkmenistan.txt", "Uganda.txt", "Ukraine.txt", "Uruguay.txt", "Uzbekistan.txt", "Vanuatu.txt", "Venezuela.txt", "Vietnam.txt", "W. Sahara.txt", "Yemen.txt", "Zambia.txt", "Zimbabwe.txt");

foreach ($files as $file) {
  $all_data = json_decode(file_get_contents("dataResults/topTen/$file"));

  if (empty($all_data)) {
    continue;
  }

  $results = array();
  $queries = array();

  // create results array, initializing each word
  foreach ($all_data as $word) {
    $data = array("country" => $word[2], "score" => $word[3]);
    $results[$word[0]] = $data;
    array_push($queries, $word[0]);
  }

  // get densities for each continent
  $continent_results = array(
    "south_america" => getContinentDensity($south_america, $queries),
    "oceania" => getContinentDensity($oceania, $queries),
    "north_america" => getContinentDensity($north_america, $queries),
    "europe" => getContinentDensity($europe, $queries),
    "asia" => getContinentDensity($asia, $queries),
    "africa" => getContinentDensity($africa, $queries),
    "antartica" => getContinentDensity($antartica, $queries)
  );

  // get densities for each author
  $author_results = array(
    "milton" => getAuthorDensity("milton.txt", $queries),
    "shakespeare" => getAuthorDensity ("shakespeare.txt", $queries)
  );

  // put together results array
  foreach($queries as $word) {
    $corpus_occurences = 0;
    $corpus_words = 0;
    foreach (array_keys($continent_results) as $result) {
      $continent_occurences = $continent_results[$result][$word];
      $continent_total = $continent_results[$result]["total_words"];

      $corpus_occurences += $continent_occurences;
      $corpus_words += $continent_total;

      $results[$word][$result] = (string) $continent_occurences . "/" . (string) $continent_total;
    }

    $results[$word]["milton"] = $author_results["milton"][$word];
    $results[$word]["shakespeare"] = $author_results["shakespeare"][$word];
    $results[$word]["corpus"] = (string) $corpus_occurences . "/" . (string) $corpus_words;
  }

  file_put_contents("dataResults/finalData/$file", json_encode($results));
}

?>