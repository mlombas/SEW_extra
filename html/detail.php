<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<meta name="application-name" content="geimagen" />
		<meta name="author" content="Mario Lombas - UO275901" />

		<link rel="stylesheet" href="../css/estilo.css" />
		<link rel="stylesheet" href="../css/estilo-pc.css" />
		<link rel="stylesheet" href="../css/estilo-movil.css" />

		<title>GeImagen - Detalle</title>
	</head>
	<body>
<?php
include "../php/bd_utils.php";

function insertRegion($db, $region) {
	if($region->getSuperregionId() != -1) {
		insertRegion($db, $db->region()->superregionOfRegion($region));
		echo " → ";
	}

	echo $region->getName();
}

// base de datos local
$db = new Database();

$photo_id = $_GET["id"];
$photo = $db->photo()->byId($photo_id);
$authors = $db->author()->ofPhoto($photo);
$place = $db->place()->ofPhoto($photo);
if($place->getRegionId() !== 0) 
	$region = $db->region()->ofPlace($place);
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
		<h2>Detalle de la imagen</h2>
		<article>
			<h3>"<?php echo $photo->getName(); ?>"</h3>
			<picture>
				<img 
					alt="<?php echo $photo->getName(); ?>" 
					src="<?php echo "/imagenes/" . $photo->getLink(); ?>" 
				/>
			</picture>
			<p><?php echo $photo->getDescription() ?></p>
			<section>
				<h4>Lugar</h4>
				<p><?php echo $place->getCoordinates(); ?></p>
				<p>Región: <?php insertRegion($db, $region); ?></p>
			</section>
		</article>
		<aside>
			<h3>Información adicional</h3>
			<section>
				<h4>Fotografía</h4>
				<p>Licencia: <?php echo $photo->getLicense() ?></p>
				<p>Fecha de toma: <?php echo $photo->getTime() ?></p>
			</section>
			<section>
				<h3>Autors</h3>
				<address>
<?php
foreach($authors as $author) {
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
?>
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
