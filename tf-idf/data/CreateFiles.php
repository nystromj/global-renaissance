<?php

set_time_limit(0);
ini_set('memory_limit','2048M');

$files = array( "Afghanistan.json", "Albania.json",  "Algeria.json", "America.json", "Angola.json", "Antarctica.json", "Argentina.json", "Armenia.json", "Australia.json", "Austria.json", "Azerbaijan.json", "Bahamas.json", "Bangladesh.json", "Belarus.json", "Belgium.json", "Belize.json", "Benin.json", "Bhutan.json", "Bolivia.json", "Bosnia and Herz..json", "Botswana.json", "Brazil.json", "Brunei.json", "Bulgaria.json", "Burkina Faso.json", "Burundi.json", "Cambodia.json", "Cameroon.json", "Canada.json", "Central African Rep..json", "Chad.json", "Chile.json", "China.json", "Colombia.json", "Congo.json", "Costa Rica.json", "CoÌ‚te d'Ivoire.json", "Croatia.json", "Cuba.json", "Cyprus.json", "Czech Rep..json", "Dem. Rep. Congo.json", "Dem. Rep. Korea.json", "Denmark.json", "Djibouti.json", "Dominican Rep..json", "Ecuador.json", "Egypt.json", "El Salvador.json", "England.json", "Eq. Guinea.json", "Eritrea.json", "Estonia.json", "Ethiopia.json", "Falkland Is..json", "Fiji.json", "Finland.json", "finland_old.json", "Fr. S. Antarctic Lands.json", "France.json", "Gabon.json", "Gambia.json", "Georgia.json", "Germany.json", "Ghana.json", "Greece.json", "Greenland.json", "Guatemala.json", "Guinea-Bissau.json", "Guinea.json", "Guyana.json", "Haiti.json", "Honduras.json", "Hungary.json", "Iceland.json", "India.json", "Indonesia.json", "Iran.json", "Iraq.json", "Ireland.json", "Israel.json", "Italy.json", "Jamaica.json", "Japan.json", "Jordan.json", "Kazakhstan.json", "Kenya.json", "Korea.json", "Kosovo.json", "Kuwait.json", "Kyrgyzstan.json", "Lao PDR.json", "Latvia.json", "Lebanon.json", "Lesotho.json", "Liberia.json", "Libya.json", "Lithuania.json", "Luxembourg.json", "Macedonia.json", "Madagascar.json", "Malawi.json", "Malaysia.json", "Mali.json", "Mauritania.json", "Mexico.json", "milton.json", "Moldova.json", "Mongolia.json", "Montenegro.json", "Morocco.json", "Mozambique.json", "Myanmar.json", "N. Cyprus.json", "Namibia.json", "Nepal.json", "Netherlands.json", "New Caledonia.json", "New Zealand.json", "Nicaragua.json", "Niger.json", "Nigeria.json", "Norway.json", "Oman.json", "Pakistan.json", "Palestine.json", "Panama.json", "Papua New Guinea.json", "Paraguay.json", "Peru.json", "Philippines.json", "Poland.json", "Portugal.json", "Puerto Rico.json", "Qatar.json", "Romania.json", "Russia.json", "Rwanda.json", "S. Sudan.json", "Saudi Arabia.json", "Senegal.json", "Serbia.json", "shakespeare.json" , "Sierra Leone.json", "Slovakia.json", "Slovenia.json", "Solomon Is..json", "Somalia.json", "Somaliland.json", "South Africa.json", "Spain.json", "Sri Lanka.json", "Sudan.json", "Suriname.json", "Swaziland.json", "Sweden.json", "Switzerland.json", "Syria.json", "Taiwan.json", "Tajikistan.json", "Tanzania.json", "Thailand.json", "Timor-Leste.json", "Togo.json", "Trinidad and Tobago.json", "Tunisia.json", "Turkey.json", "Turkmenistan.json", "Uganda.json", "Ukraine.json", "Uruguay.json", "Uzbekistan.json", "Vanuatu.json", "Venezuela.json", "Vietnam.json", "W. Sahara.json", "Yemen.json", "Zambia.json", "Zimbabwe.json");

try {
  foreach ($files as $f) {
    $test = "dataResults/paths/$f";
    $place = str_replace('.json', '', $f);

    // load JSON file with filenames
    $contents = json_decode(file_get_contents($test));

    $all_relevant_text = "";

    foreach ($contents as $fname=>$year)
    {
      // load each JSON file
      $filepath = "Global Renaissance/raw/$fname";

      $text = json_decode(file_get_contents($filepath));

      // loop through each paragraph in each section
      foreach ($text as $i=>$sec) {
        foreach($sec as $type=>$s) {
          foreach($s as $m=>$para) {
            // check if place is mentioned in paragraph and concat together
            if (stripos($para, $place) !== false) {
              $all_relevant_text .= $para . ' ';
            }
          }
        }
      }
    } // foreach

    $nice_place = str_replace(' ', '_', $place);
    file_put_contents( "dataResults/concatParas/$nice_place.txt", $all_relevant_text);
    echo "Done with $place.<br/>";

  }
}
catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), " ";
}
?>