<html class="no-js" lang="en" dir="ltr">
 <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steam VR Videos</title>
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/app.css">
 </head>
 <body>
 	<div class="row">
		<div class="small-8 small-offset-5 columns">
			<nav>
				<h2>
					VR Vids
				</h2>
			</nav>
		</div>
	</div>

 	<div class="row">
		<div class="small-12 columns">
			<?php
			include 'functions.php';
			//Load the details for the database connection
			$configs = include 'config.php';

			$servername = $configs['servername'];
			$username = $configs['username'];
			$password = $configs['password'];
			$dbname = $configs['dbname'];

			$mysqli = new mysqli($servername,$username,$password,$dbname);

			// check connection
			if ($mysqli->connect_errno) {
				die("Connect failed: ".$mysqli->connect_error);
			}

			//Get the name and platforms of the games
			$query = "SELECT name,HTC,Rift,OSVR FROM list ORDER BY name";
			$result = $mysqli->query($query);


			echo "<form action='#' method='post'><div class='row'>
					<div class='small-8 small-centered columns'>
						<select name='Game'>";
			//For every row we get from the database, put it in an array
			while($row = $result->fetch_array()){
				//Echo an option that also feeds in the platform information
				echo "<option value='" . strtolower($row[0]) . "-" . $row[1] . "-" . $row[2] . "-" . $row[3] . "'>" . $row[0] . "</option>";
			}
			echo "</select>
					</div>
					<div class='small-4 columns'>
						<input type='submit' name='submit' class='button' value='Submit'>
					</div>
				</div></form>";
			?>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<?php
				echo "<div class='row small-up-1 medium-up-2 large-up-4'>";
				if(isset($_POST['submit'])){
					//Divide the concatenated string with name and platforms
					$values = explode("-",$_POST['Game']);
					//Get the name
					$name = $values[0];
					//Throw the platforms in an array for passing through to the getVideos function
					$platforms = [$values[1],$values[2],$values[3]];
					getVideos($name,$platforms);
				}
				echo "</div>";
			?>
		</div>
	</div>

    <script src="js/vendor/jquery.js"></script>
    <script src="js/vendor/what-input.js"></script>
    <script src="js/vendor/foundation.js"></script>
    <script src="js/app.js"></script>
 </body>
</html>