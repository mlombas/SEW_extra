class Util {
	static convertCoords(coords) {
		let processed;
		if(coords.match(/,/g))
			processed = coords.split(",")
				.map(c => c.trim())
				.map(parseFloat);
		else
			processed = coords.split(" ")
				.filter(c => c.length > 0)
				.map(parseFloat);

		return { latitude: processed[0], longitude: processed[1] };
	}

	static convertDirection(dir) {
		let processed;
		if(dir.match(/,/g))
			processed = dir.split(",")
				.map(c => c.trim())
				.map(parseFloat);
		else
			processed = dir.split(" ")
				.filter(c => c.length > 0)
				.map(parseFloat);

		return { 
			alpha: processed[0],
			beta: processed[1],
			gamma: processed[2] 
		};
	}
}
