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

			$query = "SELECT name FROM list ORDER BY name";
			$result = $mysqli->query($query);


			echo "<form action='#' method='post'><div class='row'>
					<div class='small-8 small-centered columns'>
						<select name='Game'>";
			while($row = $result->fetch_array()){
				echo "<option value='" . strtolower($row[0]) . "'>" . $row[0] . "</option>";
			}
			echo "</select>
					</div>
					<div class='small-4 columns'>
						<input type='submit' name='submit' class='button' value='Submit'>
					</div>
				</div></form>";
			//$time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			//echo "<row><div class='small-4 small-centered columns'>Process Time: {$time}</div></row>";
			?>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<?php
				echo "<div class='row small-up-1 medium-up-2 large-up-4'>";
				if(isset($_POST['submit'])){
					$selected_val = $_POST['Game'];  // Storing Selected Value In Variable
					getVideos($selected_val);
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