class PositionUpdater {
	#destiny;
	#movement;

	constructor(coords) {
		this.#destiny = Util.convertCoords(coords);
		this.#movement = {latitude: 0, longitude: 0};
	}

	#returnAccording({
		E, NE, N, NW, W, SW, S, SE
	}) {
		let angle = Math.atan2(
			this.#movement.latitude, this.#movement.longitude
		);
		angle = (2*Math.PI + angle) % (2*Math.PI);

		const section = Math.PI / 8;
		if(angle < section || angle >= 15*section)
			return E;
		if(angle >= section && angle < 3*section)
			return NE;
		if(angle >= 3*section && angle < 5*section)
			return N;
		if(angle >= 5*section && angle < 7*section)
			return NW;
		if(angle >= 7*section && angle < 9*section)
			return W;
		if(angle >= 9*section && angle < 11*section)
			return SW;
		if(angle >= 11*section && angle < 13*section)
			return S;
		if(angle >= 13*section && angle < 15*section)
			return SE;
	}

	#getMovement() {
		return this.#returnAccording({
			N: "Norte", S: "Sur", E: "Este", W: "Oeste",
			NE: "Noreste", NW: "Noroeste", SE: "Sureste", SW: "Suroeste"
		});
	}

	#getArrow() {
		return this.#returnAccording({
			N: "&uarr;", S: "&darr;", E: "&rarr;", W: "&larr;",
			NE: "&#8599;", NW: "&#8598;", SE: "&#8600;", SW: "&#8601;"
		});
	}

	#updateElement(element) {
		element.html(
			`Dirección a seguir: ${this.#getMovement()} ${this.#getArrow()}`
		);
	}

	#calculateMovement(pos) {
		this.#movement.latitude = this.#destiny.latitude - pos.latitude;
		this.#movement.longitude = this.#destiny.longitude - pos.longitude;
	}

	bindUpdate(element) {
		navigator.geolocation.watchPosition(geo => {
			this.#calculateMovement(geo.coords);
			this.#updateElement(element);
		});
	}
}
