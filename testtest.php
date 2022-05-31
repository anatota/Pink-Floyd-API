<?php

include 'connectdatabase.php';

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://genius.p.rapidapi.com/search?q=Pink%20Floyd",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"X-RapidAPI-Host: genius.p.rapidapi.com",
		"X-RapidAPI-Key: 4f4c064ca4msh7521e2c27ac2cf8p1f030fjsn26a08ad4a365"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

# Datas for database
$titles = [];
$release_dates = [];
$ids = [];
$urls = [];
$img_urls = [];
$img_paths = [];

$release_dates_full = [];

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	$storeData = json_decode($response);
	$hitsArray = $storeData->response->hits;

	for($i = 0; $i < count($hitsArray); $i++){
		$release_date = $hitsArray[$i]->result->release_date_for_display;
		$title = $hitsArray[$i]->result->title;
		$id = $hitsArray[$i]->result->id;
		$url = $hitsArray[$i]->result->url;
		$img_url = $hitsArray[$i]->result->song_art_image_url;

		$year = substr($release_date, strlen($release_date)-4);
		$year = (int)$year;

		array_push($ids, $id);
		array_push($titles, $title);
		array_push($release_dates, $year); 
		array_push($urls, $url);
		array_push($img_urls, $img_url);

		array_push($release_dates_full, $release_date);
	}

	$servername = "localhost";
	$username = "root";
	$password = "password";
	$dbname = "pink_floyd";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM songs";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		// while($row = $result->fetch_assoc()) {
		// 	var_dump($row);
		// }
	} else {
		for($i = 0; $i < 10; $i++){
			$url = $img_urls[$i];
			$img = "./images/img_$i.jpg";
			$img_dirname = getcwd().substr($img,1);
			array_push($img_paths, $img_dirname);
			
			file_put_contents($img, file_get_contents($url));

			$sql = "INSERT INTO songs VALUES(:id, :title, :release_date, :release_date_full, :lyrics_url, :img_url, :img_path)";
			$statement = $dbh -> prepare($sql);
			$statement -> execute(["id" => $ids[$i], "title" => $titles[$i], "release_date" => $release_dates[$i], "release_date_full" => $release_dates_full[$i], "lyrics_url" => $urls[$i], "img_url" => $img_urls[$i], "img_path" => $img_paths[$i]]); 
		}
	}
}


?>

<html>
	<head>
		<script src="https://cdn.tailwindcss.com"></script>
	</head>
	<body class="bg-slate-200">
		<div class="flex items-center flex-col">
			<form action="search.php" method="POST">
				<input type="text" name="search" placeholder="Search" class="my-5 appearance-none block w-full bg-gray-200 text-gray-700 border border-black rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white">
				<div class="flex mb-4">
				<input type="submit" value="Search" class="w-full bg-gray-400 h-12 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">
			</form>

			<form action="alldata.php" method="POST">
				<input type="submit" name="submit" value="All Data" class="w-full bg-gray-400 h-12 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">
				</div>
			</form>
		</div>
	</body>
</html>