<?php
class Photo
{
	private $id;
	private $name;
	private $link;
	private $description;
	private $time;
	private $license;
	private $place_id;

	function __construct(
		$id, $name, $link,
		$description, $time, $license,
		$place_id
	) {
		$this->id = $id;
		$this->name = $name;
		$this->link = $link;
		$this->description = $description;
		$this->time = $time;
		$this->license = $license;
		$this->place_id = $place_id;
	}

	function getId() { return $this->id; }

	function getName() { return $this->name; }

	function getLink() { return $this->link; }

	function getDescription() { return $this->description; }

	function getTime() { return $this->time; }

	function getLicense() { return $this->license; }

	function getPlaceId() { return $this->place_id; }
}

class Author
{
	private $id;
	private $name;
	private $email;
	private $address;
	private $phone_number;

	function __construct($id, $name, $email, $address, $phone_number) {
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		$this->address = $address;
		$this->phoneNumber = $phone_number;
	}

	function getId() { return $this->id; }

	function getName() { return $this->name; }

	function getEmail() { return $this->email; }

	function getAddress() { return $this->address; }

	function getPhoneNumber() { return $this->phoneNumber; }
}

class Place 
{
	private $id;
	private $coordinates;
	private $region_id;

	function __construct($id, $coordinates, $region_id) {
		$this->id = $id;
		$this->coordinates = $coordinates;
		$this->region_id = $region_id;
	}

	function getId() { return $this->id; }

	function getCoordinates() { return $this->coordinates; }

	function getRegionId() { return $this->region_id; }
}

class Region
{
	private $id;
	private $name;
	private $superregion_id;

	function __construct($id, $name, $superregion_id) {
		$this->id = $id;
		$this->name = $name;
		$this->superregion_id = $superregion_id;
	}

	function getId() { return $this->id; }

	function getName() { return $this->name; }

	function getSuperregionId() { return $this->superregion_id; }
}
?>
