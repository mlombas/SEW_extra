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

function insertPhoto($photo) {
	echo "<section>";

	echo "<h6>" . $photo->getName() . "</h6>";

	echo "<picture>";
	echo "<img";
	echo " alt=\"" . $photo->getDescription() . "\"";
	echo " src=\"/imagenes/" . $photo->getLink() . "\"";
	echo "/>";
	echo "</picture>";

	echo "<p><a href=\"/html/detail.php?id=" .
		$photo->getId() .
		"\">Go</a></p>";

	echo "</section>";
}

$db = new Database();

$photos = null;
if($_GET) {
	if(isset($_GET["author"]) && isset($_GET["region"]))
		$photos = $db->photo()->allInRegionOfAuthor(
			ascending: $_GET["order"] == "true",
			name: isset($_GET["name"]) ? $_GET["name"] : "",
			region: $db->region()->byId($_GET["region"]),
			author_name: $_GET["author"]
		);
	else if(isset($_GET["author"]))
		$photos = $db->photo()->allOfAuthor(
			ascending: $_GET["order"] == "true",
			name: isset($_GET["name"]) ? $_GET["name"] : "",
			author_name: $_GET["author"]
		);
	else if(isset($_GET["region"]))
		$photos = $db->photo()->allInRegion(
			ascending: $_GET["order"] == "true",
			name: isset($_GET["name"]) ? $_GET["name"] : "",
			region: $db->region()->byId($_GET["region"])
		);
	else
		$photos = $db->photo()->all(
			ascending: $_GET["order"] == "true",
			name: isset($_GET["name"]) ? $_GET["name"] : ""
		);
} else {
	$photos = $db->photo()->all();
}

$regions = $db->region()->all();
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
		<h2>Listado de Imágenes</h2>
		<section>
			<h3>Opciones de búsqueda</h3>
			<form
				method="GET"
				action="/html/list.php"
			>
				<p>Nombre: <label> 
					<input type="text" name="name" 
					value="<?php echo $_GET["name"] ?>"</input>
				</label></p>
				<p>Autor: <label> 
					<input type="text" name="author" 
					value="<?php echo $_GET["author"] ?>"</input>
				</label></p>
				<p>Region: <label>
					<select name="region">
<?php
foreach($regions as $region) {
	$selected = $region->getId() === (int) $_GET["region"] ?
		"selected" :
		"";
	echo "<option value=\"" . $region->getId() . "\" " .
		$selected . ">" .
		$region->getName() . "</option>";
}
?>
					</select>
				</label></p>
				<fieldset>
					<legend>Orden</legend>
					<p><label> 
						<input 
							type="radio" 
							name="order" 
							value="true"
						<?php if($_GET["order"] === "true") echo "checked" ?>
						>Ascendiente</input>
					</label></p>
					<p><label> 
						<input 
							type="radio" 
							name="order" 
							value="false"
						<?php if($_GET["order"] === "false") echo "checked" ?>
						>Descendiente</input>
					</label></p>
				</fieldset>
				<p><label> <button type=submit>Buscar</button></input></p>
			</form>
		</section>
<?php
foreach($photos as $photo) {
	insertPhoto($photo);
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
