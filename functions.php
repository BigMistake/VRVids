<?php
	include 'simple_html_dom.php';
	
	function getPage($url){
		//Add headers to file_get_contents since sometimes websites like that sort of thing
	    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $header = file_get_contents($url,false,$context);
        return $header;
	}
	          
	function getPageCount($steamURL){
		//Get first page of the Steam page to get the amount of pages
		$html = file_get_html($steamURL);
		echo "Going to the Steam Store page " . $steamURL . ".<br>";
		//From the pagination section, get the third link (link to last page) that shows the page number.
		$pages = $html->find('.search_pagination_right > a.childNodes[3]',0)->plaintext;
		echo $pages . " pages to loop through.<br>";
		
		return $pages;
	}
	
	function getAllGames($steamURL,$pages,$games){
		//Iterate though all the pages
		for ($i = 1; $i < ($pages+1); $i++) {
			//Get content of current page
			//echo "Going to page #" . $i . " " . $steamURL . "&page=" . $i . "</br>";
			$gamePage = file_get_html($steamURL . "&page=" . $i);
			//Loop through all the result rows
			$iteration = 0;
			foreach($gamePage->find('#search_result_container > div > a') as $game) {
				//For some reason it finds more than 25 results, just cap it at 25
				if($iteration < 25){
					$iteration ++;
					$array = array();
					$array['name'] = "";
					$array['HTC'] = 0;
					$array['Rift'] = 0;
					$array['OSVR'] = 0;
						
					//Get the game title from the span element, but check if it's empty first
					if(!empty($game->find('.search_name > span.title', 0)->plaintext)){
						//Get the title
						$result = $game->find('.search_name > span.title', 0)->plaintext;
						//Get rid of those pesky weird characters, save for spaces
						$string = html_entity_decode(preg_replace("/[^A-Za-z0-9\-\s]/", "", $result));
						//Push the resulting title into the array
						$array['name'] = $string;
					}
					
					//Get the platforms from the underlying span elements
					foreach($game->find('.search_name > p > span[title]') as $a){
					  switch($a->attr['title']){
						 case "HTC Vive":
							$array['HTC'] = 1;
							break;
							
						case "Oculus Rift":
							$array['Rift'] = 1;
							break;
							
						case "OSVR":
							$array['OSVR'] = 1;
							break;
							
						default:
							echo "Found nothing<br>";
					  }
					}
					
					echo $array['name'] . " met platforms: " . $array['HTC'] . " " . $array['Rift'] . " " . $array['OSVR'] . "<br>";

					//Push everything into the array that we'll be saving in the database
					$games[] = $array;
				}
			}
		}
		
		return $games;
	}
	
	function saveAllGames($games,$format){
		switch($format){
			case "JSON":
				saveToJSON($games);
				break;
			case "Database":
				saveToDatabase($games);
				break;
		}
	}

	function saveToDatabase($games){
		$configs = include 'config.php';

		$servername = $configs['servername'];
		$username = $configs['username'];
		$password = $configs['password'];
		$dbname = $configs['dbname'];

		// Create connection
		$conn = mysqli_connect($servername, $username, $password, $dbname);
		// Check connection
		if (!$conn) {
			die("Connection failed: " . mysqli_connect_error());
		}

		echo count($games);

		foreach($games as $game){
			$name = $game['name'];
			$htc = $game['HTC'];
			$rift = $game['Rift'];
			$osvr = $game['OSVR'];

			$sql = "INSERT IGNORE INTO vrvids_games.list (name,HTC,Rift,OSVR) VALUES ('$name','$htc','$rift','$osvr');";
			if (mysqli_query($conn, $sql)) {
				//echo "New record created successfully</br>";
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
		}

		mysqli_close($conn);
	}

	function saveToJSON($games){
		//Save to a json file for later
		file_put_contents("results.json",json_encode($games));
	}

	function getVideos($vrgame, $platforms){
	    //But then replace those for plus signs for the Reddit search query
		$redditstring = preg_replace("/\s/", "+",$vrgame);

		$HTC = "";
		$Rift = "";
		$OSVR = "";

		//Sorry, this is the best I can think of right now
		if($platforms[0] == 1){
			$HTC = "Vive+";
		}
		if($platforms[1] == 1){
			$Rift = "Oculus+";
		}
		if($platforms[2] == 1){
			$OSVR = "OSVR+";
		}

		//Put it together nice and pretty
		$redditurl = "https://www.reddit.com/r/" . $HTC . $Rift . $OSVR ."/search.json?q=" . $redditstring . "site%3Ayoutube&restrict_sr=on&sort=relevance&t=all";
		//Actually get the page now
		$source = getPage($redditurl);
		//We want it in json of course
		$json = json_decode($source, true);
		
		//Check if there's anything to loop through otherwise the foreach loop will cause trouble
		if(count($json['data']['children']) == 0){
		    file_put_contents("MissedGames.txt",$vrgame . "\r\n",FILE_APPEND);
		    return;
		}
		
		//Loop through all found entries, which *should* only be YouTube videos. 
		foreach($json['data']['children'] as $child){
			//get the link to the YT thumbnail, after decoding it
			$url = urldecode($child['data']['secure_media']['oembed']['thumbnail_url']);
			//Get the version without black bars (mostly)
			$cropped = preg_replace("/hqdefault/", "mqdefault", $url);
			//Get the YT Id
			$id = preg_replace("/.*\/vi\/(.*)\/hqdefault\.jpg/", "$1", $url);
			//Prepare YT Data API call
			$check = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=" . $id . "&key=AIzaSyCmMpjNlTOIGQTFDTu8XnJ46zvlCfpuoU0&part=statistics"), true);
			//Using the YT id and API, check if it has any statistics. If not, then it's not available and we don't want it anyway
			if($check['pageInfo']['totalResults'] == 0){
				continue;
			}
			else {
				echo "<div class='column video'>";
					//Use the information from the JSON search results to get the thumbnail url/width/height/title
					echo "<a href=\"" . $child['data']['url'] . "\"><img src='" . $cropped . "' height='" . $child['data']['secure_media']['oembed']['thumbnail_height'] . "' width='" . $child['data']['secure_media']['oembed']['thumbnail_width'] . "' title='" . $child['data']['title'] . "' ></a>";
				echo "</div>";
			}
		}
			
		
	}
?>