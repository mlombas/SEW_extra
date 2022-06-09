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

$db = new Database();

function makeRegionSection($region, $db) {
	echo "<section>";
	echo "<h4>" . $region->getName() . "</h4>";
	echo "<p>ID: " . $region->getId() . "</p>";
	echo "<h6>Subregions: </h6>";
	$subregions = $db->region()->bySuperregion($region);
	foreach($subregions as $subregion) makeRegionSection($subregion, $db);
	echo "</section>";
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
			<h2>Listado de regiones</h2>
<?php
$regions = $db->region()->withNoSuperregion();
foreach($regions as $region) 
	makeRegionSection($region, $db);
?>
		</main>
		<footer>
			<h3>Información adicional</h3>
			<p>Autor: Mario Lombas</p>
			<p>27/05/2022</p>
		</footer>
	</body>
</html>
