<?php

include "connectdatabase.php";
include "testtest.php";

$keyword = trim($_POST["search"]);
$sql = "SELECT * FROM songs WHERE title LIKE '%$keyword%'";
$result = $conn->query($sql);


if ($result->num_rows > 0):
    while($row = $result->fetch_assoc()):
        ?>
            <div class="flex items-center flex-col my-10">
                <h2><strong>ID: <?php print $row["id"]; ?></strong></h2>
                <br>
                <h3><?php echo $row["title"] . " was released on " . $row["release_date_full"]; ?></h3>
                <br>
                <h4><a href=<?php print $row["lyrics_url"];?> target="_blank">Click for lyrics</a></h4>
                <br>
                <h6><?php echo "Photo is stored at " . $row["img_path"]; ?></h6>
                <br>
                <img class="object-scale-down h-100 w-96 my-5 rounded-lg" src='<?php print $row["img_url"] ?>'>
                <br>
        </div>
<?php
        endwhile;
    else:
?> 
<?php
        searchInAPI($keyword);
endif;
?>

<?php

function searchInAPI($key){
    searchByKeyword($key);
}

function searchByKeyword($keyword){
    
    for($i = 0; $i < strlen($keyword); $i++){
        if($keyword[$i] === " "){
            $keyword = str_replace(" ", "%20", $keyword);
        }
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://genius.p.rapidapi.com/search?q=$keyword",
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


    # New arrays for new data
    $ids_new = [];
    $titles_new = [];
    $release_dates_new = [];
    $urls_new = [];
    $img_urls_new = [];
    $img_paths_new = [];

    $release_dates_full_new = [];

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $response_JSON = json_decode($response);

        $hits_array = $response_JSON->response->hits;
        foreach($hits_array as $array){
            if($array->result->primary_artist->name == "Pink Floyd"){
                $release_date = $array->result->release_date_for_display;
                $title = $array->result->title;
                $id = $array->result->id;
                $url = $array->result->url;
                $img_url = $array->result->song_art_image_url;

                $year = substr($release_date, strlen($release_date)-4);
                $year = (int)$year;

                array_push($ids_new, $id);
                array_push($titles_new, $title);
                array_push($release_dates_new, $year); 
                array_push($urls_new, $url);
                array_push($img_urls_new, $img_url);

                array_push($release_dates_full_new, $release_date);

                break;
            }
        }
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

	
    for($i = 0; $i < count($ids_new); $i++):
        $url = $img_urls_new[$i];
        $img = "./images/img_new_$i.jpg";
        $img_dirname = getcwd().substr($img,1);
        array_push($img_paths_new, $img_dirname);

        file_put_contents($img, file_get_contents($url));

        $value_id = $ids_new[$i];
        $value_title = $titles_new[$i];
        $value_release_date = $release_dates_new[$i];
        $value_release_dates_full = $release_dates_full_new[$i];
        $value_url = $urls_new[$i];
        $value_img_url = $img_urls_new[$i];
        $value_img_path = $img_paths_new[$i];

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO songs VALUES(:id, :title, :release_date, :release_date_full, :lyrics_url, :img_url, :img_path)";
        $statement = $conn -> prepare($sql);
        $statement -> execute(["id" => $ids_new[$i], "title" => $titles_new[$i], "release_date" => $release_dates_new[$i], "release_date_full" => $release_dates_full_new[$i], "lyrics_url" => $urls_new[$i], "img_url" => $img_urls_new[$i], "img_path" => $img_paths_new[$i]]); 

        ?> 

        <p class="text-center">New record created successfully</p>
        <p class="text-center text-sky-800"><strong>Click All Data to see updated database</strong></p>


    <?php endfor; ?>   
	
<?php }
