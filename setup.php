<?php
    include 'functions.php';
	
	//Because we need to be naughty and take a longer while to do things.
	ini_set('max_execution_time', 400);
	//Clear out the file keeping track of games without videos
	file_put_contents("MissedGames.txt","");

	//Array to hold all game names
	$games = array();
	//Page of the search we want to use
	$steamURL = "http://store.steampowered.com/search/?sort_by=Name_ASC&category1=998,994,997&vrsupport=402,401";
	
	//Check the amount of pages to load
	$pages = getPageCount($steamURL);
	//Scrape all game names
	$games = getAllGames($steamURL,$pages,$games);
	//Save them all in the database
	saveAllGames($games);
?>