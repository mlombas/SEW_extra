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

<?php
include "../php/bd_utils.php";

class AuthorPrinter
{
	private $authors;

	function __construct($authors) {
		$this->authors = $authors;
	}

	function printAuthors() {
		foreach($this->authors as $author) {
			echo "<ul>";

			if($author->getName() != "")
				echo "<li><a rel=\"author\" href=\"#\">" .
				$author->getName() . "</a></li>";
			if($author->getEmail() != "")
				echo "<li><a rel=\"author\" href=\"#\">" .
				$author->getEmail() . "</a></li>";
			if($author->getAddress() != "")
				echo "<li><a rel=\"author\" href=\"#\">" .
				$author->getAddress() . "</a></li>";
			if($author->getPhoneNumber() != "")
				echo "<li><a rel=\"author\" href=\"#\">" .
				$author->getPhoneNumber() . "</a></li>";

			echo "</ul>";
		}
	}
}

class PhotoDetailMaker 
{
	private $db;
	private $photo;
	private $authors;
	private $place;
	private $region;

	function __construct($photo_id) {
		$this->db = new Database();

		$this->photo = $this->db->photo()->byId($photo_id);
		$this->authors = $this->db->author()->ofPhoto($this->photo);
		$this->place = $this->db->place()->ofPhoto($this->photo);
		if($this->place->getRegionId() !== 0) 
			$this->region = $this->db->region()->ofPlace($this->place);
		else
			$this->region = null;
		$this->direction = $this->db->direction()->ofPhoto($this->photo);
	}

	function getName() {
		return $this->photo->getName();
	}

	function getLink() {
		return $this->photo->getLink();
	}

	function getDescription() {
		return $this->photo->getDescription();
	}

	function getLicense() {
		return $this->photo->getLicense();
	}

	function getTime() {
		return $this->photo->getTime();
	}

	function getDateString() {
		$date = strtotime($this->photo->getTime());
		$daystr = date("d", $date);
		$monthstr = date("m, Y", $date);
		$timestr = date("h:ia", $date);

		return $daystr . " del " . $monthstr .
			" a las " . $timestr;
	}

	function getAuthorPrinter() {
		return new AuthorPrinter($this->authors);
	}

	function getCoordinates() {
		return $this->place->getCoordinates();
	}

	function printDirection() {
		if(!$this->direction) return;

		echo "<section>";

		echo "<h3>Dirección de la cámara</h3>";

		echo "<p>Posición exacta: " . $this->direction->getPosition() .
			"</p>";
		echo "<h3>Indicaciones de orientación:</h3>";
		echo "<p>Cargando Orientación...</p>";

		echo "</section>";
	}

	function getOrientation() {
		if(!$this->direction) return null;
		return $this->direction->getDirection();
	}

	function getRegionString() {
		$chain = $this->getRegionChain($this->region);
		return join(" &rarr; ", $chain);
	}

	private function getRegionChain($region) {
		$regions = [$region->getName()];
		if($region->getSuperregionId() != -1) {
			$new = $this->getRegionChain(
				$this->db->region()->superregionOfRegion($region)
			);
			$regions = array_merge($new, $regions);
		}

		return $regions;
	}
}

$maker = new PhotoDetailMaker($_GET["id"]);
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="../js/Util.js"></script>
<script src="../js/DateConversor.js"></script>
<script src="../js/PositionUpdater.js"></script>
<script src="../js/DirectionUpdater.js"></script>
<script>
"use strict";
class PageManager
{
	#dateConversor;
	#positionUpdater;
	#directionUpdater;

	constructor(coords, direction, time) {
		this.#dateConversor = new DateConversor(time, coords);
		this.#positionUpdater = new PositionUpdater(coords);
		this.#directionUpdater = new DirectionUpdater(direction);

		$(window).on("load", this.#bindAll.bind(this));
	}

	#bindAll() {
		this.#dateConversor.convertedModify($("p:contains('Hora')"));
		this.#dateConversor.dayPeriodModify($("p:contains('Periodo')"));

		this.#positionUpdater.bindUpdate($("p:contains('Movimiento')")); 

		this.#directionUpdater.bindUpdate($("p:contains('Orientación')"));
	}
}

let page = new PageManager(
	"<?php echo $maker->getCoordinates(); ?>",
	"<?php echo $maker->getOrientation() ?>",
	"<?php echo $maker->getTime(); ?>"
);
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
		<h1>Detalle de la imagen</h1>
		<article>
			<h2>"<?php echo $maker->getName(); ?>"</h2>
			<picture>
				<img 
					alt="<?php echo $maker->getName(); ?>" 
					src="<?php echo "/imagenes/" . $maker->getLink(); ?>" 
				/>
			</picture>
			<p><?php echo $maker->getDescription() ?></p>
			<section>
				<h3>Lugar</h3>
				<p>Coordenadas: <?php echo $maker->getCoordinates(); ?></p>
				<p>Cargando Movimiento...</p>
				<p>Región: <?php echo $maker->getRegionString(); ?></p>
			</section>
			<?php $maker->printDirection(); ?>
		</article>
		<aside>
			<h2>Información adicional</h2>
				<p>Licencia: <?php echo $maker->getLicense() ?></p>
			<section>
				<h3>Fecha</h3>
				<p>Cargando Periodo...</p>
				<h3>Donde la imágen fué tomada:</h3>
				<p><?php echo $maker->getDateString(); ?></p>
				<h3>Conversión a hora local:</h3>
					<p>Cargando Hora...</p>
			</section>
			<section>
				<h3>Autores</h3>
				<address>
<?php $maker->getAuthorPrinter()->printAuthors(); ?>
				</address>
			</section>
		</aside>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
