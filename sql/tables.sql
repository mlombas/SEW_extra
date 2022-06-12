DROP TABLE photos_authors;
DROP TABLE photo;
DROP TABLE author;
DROP TABLE place;
DROP TABLE region;
DROP TABLE direction;

CREATE TABLE direction (
	direction_id INT NOT NULL AUTO_INCREMENT,
	position VARCHAR(255) NOT NULL,
	direction VARCHAR(100) NOT NULL,
	PRIMARY KEY (direction_id)
);

CREATE TABLE region (
	region_id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL,
	superregion_id INT,
	PRIMARY KEY (region_id),
	FOREIGN KEY (superregion_id) REFERENCES region(region_id)
);

CREATE TABLE place (
	place_id INT NOT NULL AUTO_INCREMENT,
	coordinates VARCHAR(100) NOT NULL,
	region_id INT,
	PRIMARY KEY (place_id),
	FOREIGN KEY (region_id) REFERENCES region(region_id)
);

CREATE TABLE photo (
	photo_id INT NOT NULL AUTO_INCREMENT,
	photo_name VARCHAR(100) NOT NULL,
	description VARCHAR(100) NOT NULL,
	photo_link VARCHAR(50) NOT NULL,
	time DATETIME NOT NULL,
	license VARCHAR(50),
	place_id INT NOT NULL,
	direction_id INT,
	PRIMARY KEY (photo_id),
	FOREIGN KEY (place_id) REFERENCES place(place_id),
	FOREIGN KEY (direction_id) REFERENCES direction(direction_id)
);

CREATE TABLE author (
	author_id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(50),
	phone_number VARCHAR(50),
	email VARCHAR(50),
	address VARCHAR(50),
	PRIMARY KEY (author_id)
);

CREATE TABLE photos_authors ( 
	photo_id INT NOT NULL,
	author_id INT NOT NULL,
	PRIMARY KEY (photo_id, author_id),
	FOREIGN KEY (photo_id) REFERENCES photo(photo_id),
	FOREIGN KEY (author_id) REFERENCES author(author_id)
);
