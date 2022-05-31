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
			(int) $row["place_id"]
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

	function all($ascending = true, $name = "") {
		$query = "SELECT * " .
			"FROM photo " .
			"WHERE photo_name LIKE ? " .
			"ORDER BY photo_name " .
			($ascending ? "ASC" : "DESC");

		$pquery = $this->db->prepare($query);
		$like_query = "%" . $name . "%";
		$pquery->bind_param("s", $like_query);
		$pquery->execute();
		$result = $pquery->get_result();

		$photos = [];
		while($row = $result->fetch_array()) {
			array_push($photos, $this->rowToPhoto($row)); 
		}

		return $photos;
	}

	function allOfAuthor($ascending = true, $name = "", $author_name) {
		$query = "SELECT DISTINCT photo.* " .
			"FROM photo, photos_authors, author " .
			"WHERE photo.photo_name LIKE ? " .
			"AND author.name LIKE ? " .
			"AND photos_authors.author_id = author.author_id " .
			"AND photos_authors.photo_id = photo.photo_id " .
			"ORDER BY photo.photo_name " .
			($ascending ? "ASC" : "DESC");

		$pquery = $this->db->prepare($query);
		$photo_name = "%" . $name . "%";
		$author_name = "%" . $author_name . "%";
		$pquery->bind_param("ss", $photo_name, $author_name);
		$pquery->execute();
		$result = $pquery->get_result();

		$photos = [];
		while($row = $result->fetch_array()) {
			array_push($photos, $this->rowToPhoto($row)); 
		}

		return $photos;
	}

	function allInRegion($ascending = true, $name = "", $region) {
		$query = "SELECT * " .
			"FROM photo, place, region " .
			"WHERE photo_name LIKE ? " .
			"AND photo.place_id = place.place_id " .
			"AND place.region_id = ? " .
			"ORDER BY photo_name " .
			($ascending ? "ASC" : "DESC");

		$subregions =
			(new RegionManager($this->db))->subregionsOfRegion($region);

		$pquery = $this->db->prepare($query);
		$like_query = "%" . $name . "%";

		$photos = [];
		$added_ids = [];
		foreach($subregions as $subregion) {
			$pquery->bind_param("si", $like_query, $subregion->getId());
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

		return $photos;
	}

	function allInRegionOfAuthor(
		$ascending = true, $name = "",
		$region, $author_name
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
		echo count($subregions);

		$pquery = $this->db->prepare($query);
		$name_query = "%" . $name . "%";
		$author_query = "%" . $author_name . "%";

		$photos = [];
		$added_ids = [];
		foreach($subregions as $subregion) {
			$pquery->bind_param("ssi", 
				$name_query,
				$author_query,
				$subregion->getId()
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

		return $photos;
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
			(int) $row["superregion_id"]
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

	function subregionsOfRegion($region) {
		$regions = $this->bySuperregion($region);

		$result = [$region];
		if(empty($regions)) return $result;

		$result = array_merge($result, $regions);
		foreach($regions as $subregion) {
			echo "subregion " . $subregion->getId();
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
}

class Database
{
	private $db;
	private $photo;
	private $author;
	private $place;
	private $region;

	function __construct() {
		$this->db =
			new mysqli("localhost", "DBUSER2021", "DBPSWD2021", "geimagen");

		$this->photo = new PhotoManager($this->db);
		$this->author = new AuthorManager($this->db);
		$this->place = new PlaceManager($this->db);
		$this->region = new RegionManager($this->db);
	}

	function photo() { return $this->photo; }
	function author() { return $this->author; }
	function place() { return $this->place; }
	function region() { return $this->region; }
}
?>
