class LocationFilter {
	#elements;

	constructor() {
		this.#elements = [];
	}

	addElement(element, coords) {
		this.#elements.push([element, Util.convertCoords(coords)]);
	}

	#difference(c1, c2) {
		return Math.sqrt(
			Math.pow(c1.latitude - c2.latitude, 2) +
			Math.pow(c1.longitude - c2.longitude, 2) 
		);
	}

	filter() {
		navigator.geolocation.getCurrentPosition(geo => {
			const pos = geo.coords;
			const margin = 0.2;
			for(let [element, coords] of this.#elements) {
				const diff = this.#difference(pos, coords);
				if(diff > margin)
					element.hide();
			}
		});
	}
}
