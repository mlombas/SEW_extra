<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<meta name="application-name" content="geimagen" />
		<meta name="author" content="Mario Lombas - UO275901" />

		<link rel="stylesheet" href="../css/estilo.css" />
		<link rel="stylesheet" href="../css/estilo-pc.css" />
		<link rel="stylesheet" href="../css/estilo-movil.css" />

		<title>GeImagen - Inicio</title>
	</head>
	<body>
<?php
include "../php/bd_utils.php";

function saveImage() {
	$dir = "../imagenes/";
	$original_name = $_FILES["image"]["name"];
	$name = pathinfo($original_name)["filename"];
	$imgFileType = strtolower(pathinfo($original_name)["extension"]);

	for(
		$i = 0;
		file_exists($dir . $name . $i . "." . $imgFileType);
		$i++
	);

	$result_name = $dir . $name . $i . "." . $imgFileType;

	return array(
		"result" => move_uploaded_file(
			$_FILES["image"]["tmp_name"],
			$result_name
		),
		"filename" => $name . $i . "." . $imgFileType
	);

}

function bad_upload() {
	echo "<h2>Ha fallado la subida de la imagen</h2>";
	echo "<p>Por favor intentelo de nuevo</h2>";
}

function good($url) {
	echo "<h2>¡Su fotografía ha sido subida con éxito!</h2>";
	echo "<p>puedes pulsar " .
		"<a href=\"" . $url . "\">aquí</a> " .
		"para verla</p>";
}
?>
		<aside>
			<h2>Navegación</h2>
			<nav>
				<ul>
					<li><a href="../html/compartir.html" accesskey="c" tabindex="1">Compartir</a> una fotografía</li>
					<li><a href="../html/explicación.html" accesskey="e" tabindex="2">Explicación</a> de la aplicación</li>
				</ul>
			</nav>
		</aside>
		<main>
<?php
	if(isset($_POST["name"])) {
		$db = new Database();

		//Before all, try to store image
		$fupload = saveImage();
		//If it doesnt work, we stop here
		if(!$fupload["result"])
			bad_upload();
		else {
			$region = null;
			//If it is numeric, user has passed us a region ID,
			//act accordingly
			if(is_numeric($_POST["region"]))
				$region = $db->region()->byId(intval($_POST["region"]));
			else //If not a number, try to get region by name
				$region = $db->region()->byName($_POST["region"]);

			//If region doesnt exists, create a new one
			if($region === null) 
				$region = $db->region()->insert(new Region(
					id: -1, name: $_POST["region"], superregion_id: null
				));

			//Add place...
			$place = new Place(
				id: -1, coordinates: $_POST["coords"],
				region_id: $region->getId()
			);
			$place = $db->place()->insert($place);

			//Add photo...
			$photo = new Photo(
				id: -1, name: $_POST["name"], link: $fupload["filename"],
				description: $_POST["description"], time: $_POST["date"],
				license: $_POST["license"], place_id: $place->getId()
			);
			$photo = $db->photo()->insert($photo);

			//Add author...
			$author = new Author(
				id: -1, name: $_POST["author_name"], 
				email: $_POST["author_email"],
				address: $_POST["author_address"],
				phone_number: $_POST["author_phone"]
			);
			$author = $db->author()->insert($author);

			//Bind author and photo
			$db->bindPhotoAuthor($photo, $author);

			//We tell the user that all nice
			good("/html/detail.php?id=" . $photo->getId());
		}
}
?>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
