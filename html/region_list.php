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

class RegionMaker
{
	private $db;

	function __construct() {
		$this->db = new Database();
	}

	function makeAllDbRegions() {
		$regions = $this->db->region()->withNoSuperregion();
		foreach($regions as $region) 
			$this->makeRegionSection($region);
	}

	private function makeRegionSection($region) {
		echo "<section>";

		echo "<h2>" . $region->getName() . "</h2>";
		echo "<p>ID: " . $region->getId() . "</p>";

		$subregions = $this->db->region()->bySuperregion($region);
		if(!empty($subregions)) {
			echo "<section>";

			echo "<h3>Sub-regiones: </h3>";
			foreach($subregions as $subregion) 
				$this->makeRegionSection($subregion);

			echo "</section>";
		}

		echo "</section>";
	}
}
?>
		<aside>
			<h1>Navegación</h1>
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
			<h1>Listado de regiones</h1>
<?php (new RegionMaker())->makeAllDbRegions(); ?>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
