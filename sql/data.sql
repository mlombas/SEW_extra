INSERT INTO region (name)
VALUES ("Mundo");
INSERT INTO region (name, superregion_id) 
VALUES ("Europa", 1);
INSERT INTO region (name, superregion_id)
VALUES ("España", 2);
INSERT INTO region (name, superregion_id)
VALUES ("Eslovenia", 2);

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
	"foto america.jpg", "2022-06-12 14:19:00", "MIT",
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

INSERT INTO place (coordinates, region_id) 
VALUES ("46.36151294698586, 14.084574983526", 4);

INSERT INTO photo (
	photo_name, description,
	photo_link, time, license,
	place_id
)
VALUES (
	"Lago", "Lago Bled, Eslovenia",
	"lake.jpg", "2012-03-23 11:32:00", "CC",
	2
);

INSERT INTO author (name, phone_number)
VALUES (
	"Maja Novak",
	"+386 384 32 32 99"
);

INSERT INTO photos_authors (photo_id, author_id)
VALUES (2, 2);

INSERT INTO author (name, email)
VALUES (
	"Franc Kovač",
	"franckovac@yahoo.com"
);

INSERT INTO photos_authors (photo_id, author_id)
VALUES (2, 3);

INSERT INTO direction (position, direction) 
VALUES ("Desde un batíscafo", "0 90 0");

INSERT INTO place (coordinates, region_id) 
VALUES ("11.349901216981433, 142.19935123607385", 1);

INSERT INTO photo (
	photo_name, description,
	photo_link, time, license,
	place_id, direction_id
)
VALUES (
	"Fosa de las Marianas", "Challenger Deep, el punto más profundo del planeta. Se puede apreciar una bolsa de plástico",
	"mariana.jpg", "2019-05-10 04:00:00", "CC",
	3, 2
);

INSERT INTO author (name)
VALUES (
	"Victor Vescovo"
);

INSERT INTO photos_authors (photo_id, author_id)
VALUES (3, 4);
