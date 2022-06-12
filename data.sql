INSERT INTO region (name)
VALUES ("Mundo");
INSERT INTO region (name, superregion_id) 
VALUES ("Europa", 1);
INSERT INTO region (name, superregion_id)
VALUES ("España", 2);

INSERT INTO direction (position, direction) 
VALUES ("Desde la terraza de una habitación", "90 0 89");

INSERT INTO place (coordinates, region_id) 
VALUES ("43.35383236046194, -5.853616417052322", 3);

INSERT INTO photo (
	photo_name, description,
	photo_link, time, license,
	place_id, direction_id
)
VALUES (
	"Montañas", "Montañas a través de la niebla desde el Colegio Mayor América",
	"foto america.jpg", "2022-05-31 12:33:55", "MIT",
	1, 1
);

INSERT INTO author (name, phone_number, email, address)
VALUES (
	"Mario Lombas",
	"618 10 00 00",
	"mariolombasc@gmail.com",
	"c/ Valdés Salas S/N Colegio Mayor América"
);

INSERT INTO photos_authors (photo_id, author_id)
VALUES (1, 1);
