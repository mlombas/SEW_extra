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

		echo "<h4>" . $region->getName() . "</h4>";
		echo "<p>ID: " . $region->getId() . "</p>";

		$subregions = $this->db->region()->bySuperregion($region);
		if(!empty($subregions)) {
			echo "<section>";

			echo "<h6>Sub-regiones: </h6>";
			foreach($subregions as $subregion) 
				$this->makeRegionSection($subregion);

			echo "</section>";
		}

		echo "</section>";
	}
}
?>
		<aside>
			<h2>Navegación</h2>
			<nav>
				<ul>
					<li><a href="../html/index.html" accesskey="i" tabindex="1">Inicio</a></li>
					<li><a href="../html/explanation.html" accesskey="e" tabindex="2">Explicación</a> de la aplicación</li>
					<li><a href="../html/post.html" accesskey="c" tabindex="3">Compartir</a> una fotografía</li>
					<li><a href="../html/list.php" accesskey="l" tabindex="4">Lista</a> de fotografías de la aplicación</li>
					<li><a href="../html/region_list.php" accesskey="r" tabindex="5">Regiones</a> presentes en la aplicación</li>
				</ul>
			</nav>
		</aside>
		<main>
			<h2>Listado de regiones</h2>
<?php (new RegionMaker())->makeAllDbRegions(); ?>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
