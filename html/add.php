<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<meta name="application-name" content="geimagen" />
		<meta name="author" content="Mario Lombas - UO275901" />

		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Fjalla+One&family=Public+Sans:wght@200&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../css/estilo.css" />
		<link rel="stylesheet" href="../css/estilo-pc.css" />
		<link rel="stylesheet" href="../css/estilo-movil.css" />

		<title>GeImagen - Inicio</title>
	</head>
	<body>
<?php
include "../php/bd_utils.php";

class PhotoAdder 
{
	private $db;
	function __construct() {
		$this->db = new Database();
	}

	private function getNextName($original_name) {
		$dir = "../imagenes/";
		$name = pathinfo($original_name)["filename"];
		$imgFileType = strtolower(pathinfo($original_name)["extension"]);

		for(
			$i = 0;
		file_exists($dir . $name . $i . "." . $imgFileType);
		$i++
		);

		$result_name = $dir . $name . $i . "." . $imgFileType;
		return $result_name;
	}

	private function saveImage() {
		$original_name = $_FILES["image"]["name"];
		$result_name = $this->getNextName($original_name);

		return array(
			"result" => move_uploaded_file(
				$_FILES["image"]["tmp_name"],
				$result_name
			),
			"filename" => basename($result_name)
		);
	}

	function addByData() {
		//Before all, try to store image
		$fupload = $this->saveImage();
		//If it doesnt work, we stop here
		if(!$fupload["result"]) return false;

		$region = null;
		//If it is numeric, user has passed us a region ID,
		//act accordingly
		if(is_numeric($_POST["region"]))
			$region = $this->db->region()->byId(intval($_POST["region"]));
		else //If not a number, try to get region by name
			$region = $this->db->region()->byName($_POST["region"]);

		//If region doesnt exists, create a new one
		if($region === null) 
			$region = $this->db->region()->insert(new Region(
				id: -1, name: $_POST["region"], superregion_id: null
			));

		//Add place...
		$place = new Place(
			id: -1, coordinates: $_POST["coords"],
			region_id: $region->getId()
		);
		$place = $this->db->place()->insert($place);

		//Add photo...
		$photo = new Photo(
			id: -1, name: $_POST["name"], link: $fupload["filename"],
			description: $_POST["description"], time: $_POST["date"],
			license: $_POST["license"], place_id: $place->getId()
		);
		$photo = $this->db->photo()->insert($photo);

		//Add author...
		$author = new Author(
			id: -1, name: $_POST["author_name"], 
			email: $_POST["author_email"],
			address: $_POST["author_address"],
			phone_number: $_POST["author_phone"]
		);
		$author = $this->db->author()->insert($author);

		//Bind author and photo
		$this->db->bindPhotoAuthor($photo, $author);

		//We tell the user that all nice
		return $photo->getId();
	}

	private function add_region($name, $superregion_id) {
		//If neither exists, then we cannot create a region
		//without a name. Bad result and return
		if(!$name) return false;

		$inserted = $this->db->region()->insert(new Region(
			id: -1, name: $name, superregion_id: $superregion_id
		));
		return $inserted;
	}

	private function make_regions($start_region) {
		//Get regions present in xml
		$present_regions = array();
		for(
			$curr_region = $start_region;
		$curr_region;
		$curr_region = $curr_region->region
		) array_unshift($present_regions, $curr_region);


		//Get regions present in db
		$db_regions = array();
		for(
			$curr_region = $start_region;
		$curr_region;
		$curr_region = $curr_region->region
		) {
			$fetched_region = null;
			if($curr_region->nombre)
				$fetched_region =
					$this->db->region()->byName($curr_region->nombre);
			else if($curr_region->id)
				$fetched_region =
					$this->db->region()->byId($curr_region->id);

			array_unshift($db_regions, $fetched_region); 
		}

		//Add neccesary regions
		$superregion_id = null;
		for($i = 0; $i < count($db_regions); $i += 1) {
			$region = $db_regions[$i];
			$potential_name = $present_regions[$i]->nombre;

			if(!$region) {
				$region = $this->add_region(
					$potential_name, $superregion_id
				);
				if(!$region) return false;

				$db_regions[$i] = $region;
			}

			$superregion_id = $region->getId();
		}

		return end($db_regions);
	}

	function addByXML() {
		if($_FILES["document"]["error"]) {
			bad_upload();
			return;
		}

		$fupload = $this->saveImage();
		//If it doesnt work, we stop here
		if(!$fupload["result"]) {
			bad_upload();
			return;
		}


		$image = simplexml_load_file($_FILES["document"]["tmp_name"]);

		//Create regions if necessary
		$region = $this->make_regions($image->lugar->region);
		if(!$region) return false;

		//Add place...
		$place = new Place(
			id: -1, coordinates: $image->lugar->coordenadas,
			region_id: $region->getId()
		);
		$place = $this->db->place()->insert($place);

		//If date is a timestamp, we must convert it
		$date = null;
		if($image->fecha->timestamp) 
			$date = date('c', intval($image->fecha->timestamp));
		else if($image->fecha->iso)
			$date = $image->fecha->iso;

		//Add photo...
		$photo = new Photo(
			id: -1, name: $image->nombre, link: $fupload["filename"],
			description: $image->descripcion, time: $date,
			license: $image->licencia, place_id: $place->getId()
		);
		$photo = $this->db->photo()->insert($photo);

		//Add author...
		$author = new Author(
			id: -1, name: $image->autor->nombre,
			email: $image->autor->email,
			address: $image->autor->direccion,
			phone_number: $image->autor->telefono
		);
		$author = $this->db->author()->insert($author);

		//Bind author and photo
		$this->db->bindPhotoAuthor($photo, $author);

		//We tell the user that all nice
		return $photo->getId();
	}
}

class Printer {
	private $adder;

	function __construct() {
		$this->adder = new PhotoAdder();
	}

	function printAll() {
		$result = null;
		if(isset($_FILES["document"])) 
			$result = $this->adder->addByXML();
		if(isset($_POST["name"])) 
			$result = $this->adder->addByData();

		if($result) 
			$this->good("/html/detail.php?id=" . $result);
		else $this->bad();
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
}
?>
		<aside>
			<h2>Navegación</h2>
			<nav>
				<ul>
					<li><a href="../html/index.html">Inicio</a></li>
					<li><a href="../html/explanation.html">Explicación</a> de la aplicación</li>
					<li><a href="../html/post.html">Compartir</a> una fotografía</li>
					<li><a href="../html/list.php">Lista</a> de fotografías de la aplicación</li>
					<li><a href="../html/region_list.php">Regiones</a> presentes en la aplicación</li>
				</ul>
			</nav>
		</aside>
		<main>
			<h1>Subiendo Fotografía</h1>
<?php (new Printer())->printAll(); ?>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
