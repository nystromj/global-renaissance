<?php

set_time_limit(0);
ini_set('memory_limit','2048M');

//$files = array("Afghanistan.json", "Albania.json",  "Algeria.json", "America.json", "Angola.json", "Antarctica.json", "Argentina.json", "Armenia.json", "Australia.json", "Austria.json", "Azerbaijan.json", "Bahamas.json", "Bangladesh.json", "Belarus.json", "Belgium.json", "Belize.json", "Benin.json", "Bhutan.json", "Bolivia.json", "Bosnia and Herz..json", "Botswana.json", "Brazil.json", "Brunei.json", "Bulgaria.json", "Burkina Faso.json", "Burundi.json", "Cambodia.json", "Cameroon.json", "Canada.json", "Central African Rep..json", "Chad.json", "Chile.json", "China.json", "Colombia.json", "Congo.json", "Costa Rica.json", "CoÌ‚te d'Ivoire.json", "Croatia.json", "Cuba.json", "Cyprus.json", "Czech Rep..json", "Dem. Rep. Congo.json", "Dem. Rep. Korea.json", "Denmark.json", "Djibouti.json", "Dominican Rep..json", "Ecuador.json", "Egypt.json", "El Salvador.json", "England.json",  "Eq. Guinea.json", "Eritrea.json", "Estonia.json", "Ethiopia.json", "Falkland Is..json", "Fiji.json", "Finland.json", "finland_old.json", "Fr. S. Antarctic Lands.json", "France.json", "Gabon.json", "Gambia.json", "Georgia.json", "Germany.json", "Ghana.json", "Greece.json", "Greenland.json", "Guatemala.json", "Guinea-Bissau.json", "Guinea.json", "Guyana.json", "Haiti.json", "Honduras.json", "Hungary.json", "Iceland.json", "India.json", "Indonesia.json", "Iran.json", "Iraq.json", "Ireland.json", "Israel.json", "Italy.json", "Jamaica.json", "Japan.json", "Jordan.json", "Kazakhstan.json", "Kenya.json", "Korea.json", "Kosovo.json", "Kuwait.json", "Kyrgyzstan.json", "Lao PDR.json", "Latvia.json", "Lebanon.json", "Lesotho.json", "Liberia.json", "Libya.json", "Lithuania.json", "Luxembourg.json", "Macedonia.json", "Madagascar.json", "Malawi.json", "Malaysia.json", "Mali.json", "Mauritania.json", "Mexico.json", "milton.json", "Moldova.json", "Mongolia.json", "Montenegro.json", "Morocco.json", "Mozambique.json", "Myanmar.json", "N. Cyprus.json", "Namibia.json", "Nepal.json", "Netherlands.json", "New Caledonia.json", "New Zealand.json", "Nicaragua.json", "Niger.json", "Nigeria.json", "Norway.json", "Oman.json", "Pakistan.json", "Palestine.json", "Panama.json", "Papua New Guinea.json", "Paraguay.json", "Peru.json", "Philippines.json", "Poland.json", "Portugal.json", "Puerto Rico.json", "Qatar.json", "Romania.json", "Russia.json", "Rwanda.json", "S. Sudan.json", "Saudi Arabia.json", "Senegal.json", "Serbia.json", "shakespeare.json" , "Sierra Leone.json", "Slovakia.json", "Slovenia.json", "Solomon Is..json", "Somalia.json", "Somaliland.json", "South Africa.json", "Spain.json", "Sri Lanka.json", "Sudan.json", "Suriname.json", "Swaziland.json", "Sweden.json", "Switzerland.json", "Syria.json", "Taiwan.json", "Tajikistan.json", "Tanzania.json", "Thailand.json", "Timor-Leste.json", "Togo.json", "Trinidad and Tobago.json", "Tunisia.json", "Turkey.json", "Turkmenistan.json", "Uganda.json", "Ukraine.json", "Uruguay.json", "Uzbekistan.json", "Vanuatu.json", "Venezuela.json", "Vietnam.json", "W. Sahara.json", "Yemen.json", "Zambia.json", "Zimbabwe.json");

$files = array("China.json", "England.json", "India.json", "America.json", "Japan.json", "France.json", "Spain.json", "Portugal.json", "Egypt.json", "Korea.json");

$paths = array(/*"China" => array("China", "Cathay"), "America" => array("America"), "Egypt" => array("Egypt"), "India" => array("India"),*/ "England" => array("England"), "France" => array("France"), "Japan" => array("Japan", "Iapan", "Iappan", "Jappan"), "Spain" => array("Spain"), "Portugal" => array("Portugal"), "Korea" => array("Korea", "Corea"), "Turk" => array("Turk"), "Moor" => array("Moor"), "Virginia" => array("Virginia"));

try {
 foreach($paths as $country=>$names) {
	
	  $topFile = "dataResults/topWords/$country.txt";
	  $topWords = json_decode(file_get_contents($topFile));
	 		  
	  $tops = [];
	  $all_relevant_text = array();

	  foreach ($topWords as $word) {
		  array_push($tops, $word[0]);
		  $all_relevant_text[$word[0]] = [];
	  }
	
	  	  
	  foreach ($names as $name) {
		  $file = "dataResults/paths/$name.json";
		  $contents = json_decode(file_get_contents($file));
		  
		  foreach ($contents as $fname=>$year) {
			  $filepath = "Global Renaissance/raw/$fname";
			  $text = json_decode(file_get_contents($filepath));
			  
			  foreach ($text as $i=>$sec) {
				foreach($sec as $type=>$s) {
				  foreach($s as $m=>$para) {
					if (stripos($para, $name) !== false) {
						$stripped = preg_replace("/[^a-zA-Z 0-9]+/", " ", $para);
					  	$parawords = explode(' ', $stripped);
					  	foreach($parawords as $curr) {
						if (in_array(strtolower($curr), $tops)) {
						  array_push($all_relevant_text[strtolower($curr)], $year);
						}
					  }
					}
				  }
				}
			  }
			  
			}
	  }

	  file_put_contents( "dataResults/newYears/$country.txt", json_encode($all_relevant_text));
 }
}
catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), " ";
}
?>