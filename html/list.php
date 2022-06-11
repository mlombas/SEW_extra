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
		<link rel="stylesheet" href="../css/lista.css" />

<?php
include "../php/bd_utils.php";

class ListManager 
{
	private $db;
	private $photos;
	private $regions;

	function __construct() {
		$this->db = new Database();

		$this->photos = null;
		if($_GET) {
			if(isset($_GET["region"]))
				$this->photos = $this->db->photo()->allInRegion(
					ascending: isset($_GET["order"]) ? 
					$_GET["order"] === "true" :
					true,
					name: isset($_GET["name"]) ? $_GET["name"] : "",
					author_name: isset($_GET["author"]) ? 
						$_GET["author"] : "",
					region: $this->db->region()->byId((int) $_GET["region"])
				);
			else
				$this->photos = $this->db->photo()->all(
					ascending: isset($_GET["order"]) ? 
					$_GET["order"] === "true" :
					true,
					name: isset($_GET["name"]) ? $_GET["name"] : "",
					author_name: isset($_GET["author"]) ? $_GET["author"] : "",
				);
		} else {
			$this->photos = $this->db->photo()->all();
		}

		$this->regions = $this->db->region()->all();
	}

	function getName() {
		if(isset($_GET["name"])) return $_GET["name"];
		else return "";
	}

	function getAuthor() {
		if(isset($_GET["author"])) return $_GET["author"];
		else return "";
	}

	function printRegionOptions() {
		foreach($this->regions as $region) {
			$selected = (
					isset($_GET["region"]) &&
					$region->getId() === (int) $_GET["region"] 
				) ?
				"selected" :
				"";
			echo "<option value=\"" . $region->getId() . "\" " .
				$selected . ">" .
				$region->getName() . "</option>";
		}
	}

	function checkedAscending() {
		if(isset($_GET["order"]) && $_GET["order"] === "true")
			echo "checked";
	}

	function checkedDescending() {
		if(isset($_GET["order"]) && $_GET["order"] !== "true")
			echo "checked";
	}

	function checkedClose() {
		if(isset($_GET["close"])) echo "checked";
	}

	function insertPhotos() {
		foreach($this->photos as $photo)
			$this->insertPhoto(
				$photo,
				$this->db->place()->byId($photo->getPlaceId())
			);
	}

	private function insertPhoto($photo, $place) {
		echo "<section>";

		echo "<h3>" . $photo->getName() . "</h3>";

		echo "<picture>";
		echo "<img";
		echo " alt=\"" . $photo->getDescription() . "\"";
		echo " src=\"/imagenes/" . $photo->getLink() . "\"";
		echo "/>";
		echo "</picture>";

		echo "<p><a href=\"/html/detail.php?id=" .
			$photo->getId() .
			"\">Ir</a></p>";

		//Add script to add to LocationFilter, if filtering
		//by place
		if(isset($_GET["close"]))
			echo "<script> loc.addElement(" .
				"$(\"h3:contains('" . $photo->getName() . "')\").parent()," .
				"\"" . $place->getCoordinates() . "\"" .
				"); </script>";

		echo "</section>";
	}
}

$manager = new ListManager();
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="../js/Util.js"></script>
<script src="../js/LocationFilter.js"></script>
<script>
let loc = new LocationFilter();
$(document).ready(() => loc.filter());
</script>

		<title>GeImagen - Detalle</title>
	</head>
	<body>
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
			<h1>Listado de Imágenes</h1>
			<section>
				<h2>Opciones de búsqueda</h2>
				<form
					method="GET"
					action="/html/list.php"
				>
					<fieldset>
						<legend>Datos</legend>
						<p>Nombre: <label> 
							<input type="text" name="name" 
							value="<?php echo $manager->getName();  ?>"
							>
							</input>
						</label></p>
						<p>Autor: <label> 
							<input type="text" name="author" 
							value="<?php echo $manager->getAuthor(); ?>"
							>
							</input>
						</label></p>
					</fieldset>
					<fieldset>
						<legend>Lugar</legend>
						<p>Region: <label>
							<select name="region">
	<?php $manager->printRegionOptions(); ?>
							</select>
						</label></p>
						<p>Cercanos a tí <label>
							<input type="checkbox" name="close"
							<?php $manager->checkedClose(); ?>
							></input>
						</label></p>
					</fieldset>
					<fieldset>
						<legend>Orden</legend>
						<p><label> 
							<input 
								type="radio" 
								name="order" 
								value="true"
	<?php $manager->checkedAscending(); ?>
							>Ascendiente</input>
						</label></p>
						<p><label> 
							<input 
								type="radio" 
								name="order" 
								value="false"
	<?php $manager->checkedDescending(); ?>
							>Descendiente</input>
						</label></p>
					</fieldset>
					<p><label> <button type=submit>Buscar</button></input></p>
				</form>
			</section>
			<section>
				<h2>Resultados</h2>
	<?php $manager->insertPhotos(); ?>
			</section>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
