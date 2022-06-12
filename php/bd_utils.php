<?php
include "objects.php";

class DBManager
{
	protected $db;

	function __construct($db) {
		$this->db = $db;
	}
}

class PhotoManager extends DBManager
{
	function __construct($db) {
		parent::__construct($db);
	}

	private function rowToPhoto($row) {
		return new Photo(
			(int) $row["photo_id"],
			$row["photo_name"],
			$row["photo_link"],
			$row["description"],
			$row["time"],
			$row["license"],
			(int) $row["place_id"],
			(int) $row["direction_id"]
		);
	}

	function byId($id) {
		$query = "SELECT * FROM photo WHERE photo_id = ?";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("i", $id);
		$pquery->execute();
		$result = $pquery->get_result();

		$row = $result->fetch_array();
		return $this->rowToPhoto($row);
	}

	function all($ascending = true, $name = "", $author = "") {
		$query = "SELECT DISTINCT photo.* " .
			"FROM photo, photos_authors, author " .
			"WHERE photo.photo_name LIKE ? " .
			"AND author.name LIKE ? " .
			"AND photos_authors.author_id = author.author_id " .
			"AND photos_authors.photo_id = photo.photo_id " .
			"ORDER BY photo.photo_name " .
			($ascending ? "ASC" : "DESC");

		$pquery = $this->db->prepare($query);
		$name_query = "%" . $name . "%";
		$author_query = "%" . $name . "%";
		$pquery->bind_param("ss", $name_query, $author_query);
		$pquery->execute();
		$result = $pquery->get_result();

		$photos = [];
		while($row = $result->fetch_array()) {
			array_push($photos, $this->rowToPhoto($row)); 
		}

		return $photos;
	}

	function allInRegion(
		$ascending = true, $name = "",
		$author_name = "", $region
	) {
		$query = "SELECT DISTINCT photo.* " .
			"FROM photo, photos_authors, author, place " .
			"WHERE photo.photo_name LIKE ? " .
			"AND author.name LIKE ? " .
			"AND photos_authors.author_id = author.author_id " .
			"AND photos_authors.photo_id = photo.photo_id " .
			"AND photo.place_id = place.place_id " .
			"AND place.region_id = ? " .
			"ORDER BY photo.photo_name " .
			($ascending ? "ASC" : "DESC");

		$subregions =
			(new RegionManager($this->db))->subregionsOfRegion($region);

		$subregions = array_merge($subregions, [$region]);
		$pquery = $this->db->prepare($query);
		$name_query = "%" . $name . "%";
		$author_query = "%" . $author_name . "%";

		$photos = [];
		$added_ids = [];
		foreach($subregions as $subregion) {
			$subregion_id = $subregion->getId();
			$pquery->bind_param("ssi", 
				$name_query,
				$author_query,
				$subregion_id
			);
			$pquery->execute();
			$result = $pquery->get_result();

			while($row = $result->fetch_array()) {
				$photo = $this->rowToPhoto($row);

				if(!in_array($photo->getId(), $added_ids)) {
					array_push($photos, $photo); 
					array_push($added_ids, $photo->getId());
				}
			}
		}

		function cmp($a, $b) {
				return strcmp($a->getName(), $b->getName());
		}
		usort($photos, "cmp");
		if(!$ascending) $photos = array_reverse($photos);

		return $photos;
	}

	function insert($photo) {
		$query = "INSERT INTO photo (" .
			"photo_name, photo_link, description, place_id," .
 			"time, license, direction_id" .
			") VALUES (?, ?, ?, ?, ?, ?, ?)";
		
		$pquery = $this->db->prepare($query);

		$name = $photo->getName();
		$link = $photo->getLink();
		$description = $photo->getDescription();
		$placeId = $photo->getPlaceId();
		$time = $photo->getTime();
		$license = $photo->getLicense();
		$directionId = $photo->getDirectionId();

		$pquery->bind_param("sssissi",
			$name, $link, $description,
			$placeId, $time, $license,
			$directionId
		);
		$pquery->execute();

		$return_query =
			"SELECT * FROM photo WHERE photo_id = LAST_INSERT_ID()";
		$pquery = $this->db->prepare($return_query);
		$pquery->execute();
		$result = $pquery->get_result();
		$row = $result->fetch_array();

		return $this->rowToPhoto($row);
	}
} 

class AuthorManager extends DBManager
{
	function __construct($db) {
		parent::__construct($db);
	}

	private function rowToAuthor($row) {
		return new Author(
			(int) $row["author_id"],
			$row["name"],
			$row["email"],
			$row["address"],
			$row["phone_number"]
		);
	}

	function byPhotoId($id) {
		$query = "SELECT author.* FROM photos_authors, author " .
			"WHERE photos_authors.photo_id = ? " .
			" AND author.author_id = photos_authors.author_id " .
			"ORDER BY author.name DESC";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("i", $id);
		$pquery->execute();
		$result = $pquery->get_result();

		$authors = [];
		while($row = $result->fetch_array()) {
			array_push($authors, $this->rowToAuthor($row));
		}

		return $authors;
	}

	function ofPhoto($photo) {
		return $this->byPhotoId($photo->getId());
	}

	function insert($author) {
		$query = "INSERT INTO author " .
			"(name, phone_number, email, address) " .
			"VALUES (?, ?, ?, ?)";
		
		$pquery = $this->db->prepare($query);
		$name = $author->getName();
		$phone_number = $author->getPhoneNumber();
		$email = $author->getEmail();
		$address = $author->getAddress();
		$pquery->bind_param("ssss",
			$name, $phone_number, $email, $address
		);
		$pquery->execute();

		$return_query =
			"SELECT * FROM author WHERE author_id = LAST_INSERT_ID()";
		$pquery = $this->db->prepare($return_query);
		$pquery->execute();
		$result = $pquery->get_result();
		$row = $result->fetch_array();

		return $this->rowToAuthor($row);
	}
}

class PlaceManager extends DBManager
{
	function __construct($db) {
		parent::__construct($db);
	}

	private function rowToPlace($row) {
		return new Place(
			(int) $row["place_id"],
			$row["coordinates"],
			(int) $row["region_id"]
		);
	}

	function byId($id) {
		$query = "SELECT * " .
			"FROM place " .
			"WHERE place_id = ?";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("i", $id);
		$pquery->execute();
		$result = $pquery->get_result();

		$row = $result->fetch_array();
		return $this->rowToPlace($row);
	}

	function ofPhoto($photo) {
		return $this->byId($photo->getPlaceId());
	}

	function insert($place) {
		$query = "INSERT INTO place (region_id, coordinates) " .
			"VALUES (?, ?)";
		
		$pquery = $this->db->prepare($query);
		$region = $place->getRegionId();
		$coords = $place->getCoordinates();
		$pquery->bind_param("is", $region, $coords);
		$pquery->execute();

		$return_query =
			"SELECT * FROM place WHERE place_id = LAST_INSERT_ID()";
		$pquery = $this->db->prepare($return_query);
		$pquery->execute();
		$result = $pquery->get_result();
		$row = $result->fetch_array();

		return $this->rowToPlace($row);
	}
}

class RegionManager extends DBManager
{
	function __construct($db) {
		parent::__construct($db);
	}

	private function rowToRegion($row) {
		return new Region(
			(int) $row["region_id"],
			$row["name"],
			$row["superregion_id"] !== null ?
				(int) $row["superregion_id"] :
				-1
		);
	}

	function byId($id) {
		$query = "SELECT * " .
			"FROM region " .
			"WHERE region_id = ?";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("i", $id);
		$pquery->execute();
		$result = $pquery->get_result();

		$row = $result->fetch_array();
		return $this->rowToRegion($row);
	}

	function ofPlace($place) {
		return $this->byId($place->getRegionId());
	}

	function superregionOfRegion($region) {
		return $this->byId($region->getSuperregionId());
	}

	function bySuperregionId($id) {
		$query = "SELECT * " .
			"FROM region " .
			"WHERE superregion_id = ?";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("i", $id);
		$pquery->execute();
		$result = $pquery->get_result();

		$regions = [];
		while($row = $result->fetch_array())
			array_push($regions, $this->rowToRegion($row));

		return $regions;
	}

	function bySuperregion($region) {
		return $this->bySuperregionId($region->getId());
	}

	function byName($region_name) {
		$query = "SELECT * " .
			"FROM region " .
			"WHERE name = ?";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("s", $region_name);
		$pquery->execute();
		$result = $pquery->get_result();

		$row = $result->fetch_array();
		if(empty($row)) 
			return null;
		else return $this->rowToRegion($row);
	}

	function subregionsOfRegion($region) {
		$regions = $this->bySuperregion($region);

		$result = [];
		if(empty($regions)) return $result;

		$result = array_merge($result, $regions);
		foreach($regions as $subregion) {
			if($subregion->getId() !== null)
				$result = array_merge(
					$result,
					$this->subregionsOfRegion($subregion)
				);
		}

		return $result;
	}

	function all() {
		$query = "SELECT * " .
			"FROM region";

		$pquery = $this->db->prepare($query);
		$pquery->execute();
		$result = $pquery->get_result();

		$regions = [];
		while($row = $result->fetch_array())
			array_push($regions, $this->rowToRegion($row));

		return $regions;
	}

	function withNoSuperregion() {
		$query = "SELECT * " .
			"FROM region " .
			"WHERE region.superregion_id IS NULL";

		$pquery = $this->db->prepare($query);
		$pquery->execute();
		$result = $pquery->get_result();

		$regions = [];
		while($row = $result->fetch_array())
			array_push($regions, $this->rowToRegion($row));

		return $regions;
	}

	function insert($region) {
		$query = "INSERT INTO region (" .
			"name, superregion_id" .
			") VALUES (?, ?)";
		
		$pquery = $this->db->prepare($query);

		$name = $region->getName();
		$superregion_id = $region->getSuperregionId();

		$pquery->bind_param("si",
			$name, $superregion_id
		);
		$pquery->execute();

		$return_query =
			"SELECT * FROM region WHERE region_id = LAST_INSERT_ID()";
		$pquery = $this->db->prepare($return_query);
		$pquery->execute();
		$result = $pquery->get_result();
		$row = $result->fetch_array();

		return $this->rowToRegion($row);
	}
}

class DirectionManager extends DBManager
{
	function __construct($db) {
		parent::__construct($db);
	}

	private function rowToDirection($row) {
		return new Direction(
			(int) $row["direction_id"],
			$row["position"],
			$row["direction"]
		);
	}

	function byId($id) {
		if(!$id) return null;

		$query = "SELECT * " .
			"FROM direction " .
			"WHERE direction_id = ?";

		$pquery = $this->db->prepare($query);
		$pquery->bind_param("i", $id);
		$pquery->execute();
		$result = $pquery->get_result();

		$row = $result->fetch_array();
		return $this->rowToDirection($row);
	}

	function ofPhoto($photo) {
		return $this->byId($photo->getDirectionId());
	}

	function insert($direction) {
		$query = "INSERT INTO direction (" .
			"position, direction" .
			") VALUES (?, ?)";
		
		$pquery = $this->db->prepare($query);

		$position = $direction->getPosition();
		$dir_coords = $direction->getDirection();

		$pquery->bind_param("ss",
			$position, $dir_coords
		);
		$pquery->execute();

		$return_query =
			"SELECT * FROM direction WHERE direction_id = LAST_INSERT_ID()";
		$pquery = $this->db->prepare($return_query);
		$pquery->execute();
		$result = $pquery->get_result();
		$row = $result->fetch_array();

		return $this->rowToDirection($row);
	}
}

class Database
{
	private $db;
	private $photo;
	private $author;
	private $place;
	private $region;
	private $direction;

	function __construct() {
		$this->db =
			new mysqli("localhost", "DBUSER2021", "DBPSWD2021", "geimagen");

		$this->photo = new PhotoManager($this->db);
		$this->author = new AuthorManager($this->db);
		$this->place = new PlaceManager($this->db);
		$this->region = new RegionManager($this->db);
		$this->direction = new DirectionManager($this->db);
	}

	function photo() { return $this->photo; }
	function author() { return $this->author; }
	function place() { return $this->place; }
	function region() { return $this->region; }
	function direction() { return $this->direction; }

	function bindPhotoAuthor($photo, $author) {
		$query = "INSERT INTO photos_authors " .
			"(photo_id, author_id) " .
			"VALUES (?, ?)";

		$pquery = $this->db->prepare($query);
		$photo_id = $photo->getId();
		$author_id = $author->getId();
		$pquery->bind_param("ii", $photo_id, $author_id);
		$pquery->execute();
	}
}
?>
