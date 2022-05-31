<?php
    include "connectdatabase.php";
    include "testtest.php";

?>
<body class="bg-sky-200">
		<div class="flex items-center flex-col my-10">
			<?php
				while($row = $result->fetch_assoc()):
					?>
					<h2 class="text-lg"><strong>ID: <?php print $row["id"]; ?></strong></h2>
					<br>
					<h3 class="text-lg"><?php echo $row["title"] . " was released on " . $row["release_date_full"]; ?></h3>
					<br>
					<h4 class="text-lg text-blue-500"><a href=<?php print $row["lyrics_url"];?> target="_blank"><strong>Click for Lyrics' Meaning</strong></a></h4>
					<br>
					<h6 class="text-base"><?php echo "Photo is stored at " . $row["img_path"]; ?></h6>
				 	<br>
					<img class="object-scale-down h-100 w-96 my-5 rounded-lg" src='<?php print $row["img_url"] ?>'>
					<br>
			<?php endwhile; ?>
		</div>
</body>
