class DirectionUpdater
{
	#direction;
	#ticked;

	constructor(direction) {
		if(direction) 
			this.#direction = Util.convertDirection(direction);

		this.#ticked = false;
	}

	#getArrow(da, db, dg) {
		const margin = 2*Math.PI / 4;
		if(Math.abs(dg) > margin) {
			if(dg > 0) return "&rarr;";
			else return "&larr;";
		}

		if(Math.abs(db) > margin) {
			if(db > 0) return "&uarr;";
			else return "&darr;";
		}

		if(Math.abs(da) > margin) {
			if(da > 0) return "&#8634;";
			else return "&#8635;";
		}

		return "·";
	}

	bindUpdate(element) {
		//Cannot use jquery, as the event doesnt exist
		window.addEventListener('deviceorientation',
			({ absolute, alpha, beta, gamma }) => {
				if(alpha === null || beta === null || gamma === null) {
					element.html(
						"No se puede saber la orientación del dispositivo"
					);
					return;
				}

				const da = this.#direction.alpha - alpha;
				const db = this.#direction.beta - beta;
				const dg = this.#direction.gamma - gamma;

				element.html(this.#getArrow(da, db, dg));
			},
			true
		);

		setTimeout(() => {
			element.html(
				"Tu dispositivo parece no disponer de orientación"
			);
		},3 * 1000);
	}
}
