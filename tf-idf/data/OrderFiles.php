<?php

set_time_limit(0);
ini_set('memory_limit','512M');

$files = array("Afghanistan.txt", "Albania.txt", "Algeria.txt", "America.txt", "Angola.txt", "Antarctica.txt", "Argentina.txt", "Armenia.txt", "Australia.txt", "Austria.txt", "Azerbaijan.txt", "Bahamas.txt", "Bangladesh.txt", "Belarus.txt", "Belgium.txt", "Belize.txt", "Benin.txt", "Bhutan.txt", "Bolivia.txt", "Bosnia and Herz..txt", "Botswana.txt", "Brazil.txt", "Brunei.txt", "Bulgaria.txt", "Burkina Faso.txt", "Burundi.txt", "Cambodia.txt", "Cameroon.txt", "Canada.txt", "Central African Rep..txt", "Chad.txt", "Chile.txt", "China.txt", "Colombia.txt", "Congo.txt", "Costa Rica.txt", "CoÌ‚te d'Ivoire.txt", "Croatia.txt", "Cuba.txt", "Cyprus.txt", "Czech Rep..txt", "Dem. Rep. Congo.txt", "Dem. Rep. Korea.txt", "Denmark.txt", "Djibouti.txt", "Dominican Rep..txt", "Ecuador.txt", "Egypt.txt", "El Salvador.txt", "England.txt", "Eq. Guinea.txt", "Eritrea.txt", "Estonia.txt", "Ethiopia.txt", "Falkland Is..txt", "Fiji.txt", "Finland.txt", "Fr. S. Antarctic Lands.txt", "France.txt", "Gabon.txt", "Gambia.txt", "Georgia.txt", "Germany.txt", "Ghana.txt", "Greece.txt", "Greenland.txt", "Guatemala.txt", "Guinea-Bissau.txt", "Guinea.txt", "Guyana.txt", "Haiti.txt", "Honduras.txt", "Hungary.txt", "Iceland.txt", "India.txt", "Indonesia.txt", "Iran.txt", "Iraq.txt", "Ireland.txt", "Israel.txt", "Italy.txt", "Jamaica.txt", "Japan.txt", "Jordan.txt", "Kazakhstan.txt", "Kenya.txt", "Korea.txt", "Kosovo.txt", "Kuwait.txt", "Kyrgyzstan.txt", "Lao PDR.txt", "Latvia.txt", "Lebanon.txt", "Lesotho.txt", "Liberia.txt", "Libya.txt", "Lithuania.txt", "Luxembourg.txt", "Macedonia.txt", "Madagascar.txt", "Malawi.txt", "Malaysia.txt", "Mali.txt", "Mauritania.txt", "Mexico.txt",  "Moldova.txt", "Mongolia.txt", "Montenegro.txt", "Morocco.txt", "Mozambique.txt", "Myanmar.txt", "N. Cyprus.txt", "Namibia.txt", "Nepal.txt", "Netherlands.txt", "New Caledonia.txt", "New Zealand.txt", "Nicaragua.txt", "Niger.txt", "Nigeria.txt", "Norway.txt", "Oman.txt", "Pakistan.txt", "Palestine.txt", "Panama.txt", "Papua New Guinea.txt", "Paraguay.txt", "Peru.txt", "Philippines.txt", "Poland.txt", "Portugal.txt", "Puerto Rico.txt", "Qatar.txt", "Romania.txt", "Russia.txt", "Rwanda.txt", "S. Sudan.txt", "Saudi Arabia.txt", "Senegal.txt", "Serbia.txt", "Sierra Leone.txt", "Slovakia.txt", "Slovenia.txt", "Solomon Is..txt", "Somalia.txt", "Somaliland.txt", "South Africa.txt", "Spain.txt", "Sri Lanka.txt", "Sudan.txt", "Suriname.txt", "Swaziland.txt", "Sweden.txt", "Switzerland.txt", "Syria.txt", "Taiwan.txt", "Tajikistan.txt", "Tanzania.txt", "Thailand.txt", "Timor-Leste.txt", "Togo.txt", "Trinidad and Tobago.txt", "Tunisia.txt", "Turkey.txt", "Turkmenistan.txt", "Uganda.txt", "Ukraine.txt", "Uruguay.txt", "Uzbekistan.txt", "Vanuatu.txt", "Venezuela.txt", "Vietnam.txt", "W. Sahara.txt", "Yemen.txt", "Zambia.txt", "Zimbabwe.txt");

$i=0;

// function to sort csv files by the third value (tf-idf)
function usortThird($a, $b) {
  if($a[3] > $b[3]) {
    return -1;
  }
  else if ($a[3] < $b[3]) {
      return 1;
    }
  else {
    return 0;
  }
}


include_once( 'data/stopwords.php' );

foreach ($files as $file) {
  $place = strtolower(str_replace('.txt', '', $file));
  $all_data = [];

  if (($handle = fopen("dataResults/commasep/$file", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

      // if word is not a stopword or a derivation of country name, append to results
      if (! in_array($data[0], $stopwords) && substr($data[0], 0, strlen($place)) !== $place ) {
        array_push($all_data, $data);
      }
    } // if

    // sort results and dump
    usort($all_data, "usortThird");
    file_put_contents("dataResults/sorted/$file", json_encode($all_data));

  }
}
echo "Fin.";

?>