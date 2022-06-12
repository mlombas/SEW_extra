const API_URI = "http://api.geonames.org/timezoneJSON?";

class DateConversor {
	#date;
	#coords;
	#fetched;
	#result;

	constructor(date, coords) {
		this.#date = new Date(date);
		this.#coords = Util.convertCoords(coords);
		this.#fetched = false;
	}

	#getUri() {
		let year = this.#date.getFullYear();
		let month = this.#date.getMonth();
		let day = this.#date.getDay();
		let formattedDate = `${year}-${month}-${day}`;
		return `${API_URI}`+
			`lat=${this.#coords.latitude}&lng=${this.#coords.longitude}` +
			`&date=${formattedDate}` +
			`&username=geimagen`;
	}

	async #fetchData() {
		return new Promise((resolve, reject) => {
			if(this.#fetched) resolve();

			$.ajax({
				dataType: "json",
				url: this.#getUri(),
				method: 'GET',
				success: (json) => {
					if(json.status !== undefined) return;

					this.#fetched = true;
					this.#result = {
						sunset: json.dates?.[0]?.sunset,
						sunrise: json.dates?.[0]?.sunrise,
						difference: json.rawOffset,
					};

					resolve();
				},
			});
		});
	}

	isFetched() {
		return this.#fetched;
	}

	async getConverted() {
		await this.#fetchData();
		if(!this.#fetched) return false;

		let curr_difference = new Date().getTimezoneOffset() / 2 % 60;
		let total_difference = curr_difference - this.#result.difference;

		let date = new Date(this.#date);
		date.setHours(date.getHours() + total_difference + 1);

		return date;
	}

	async getDifference() {
		await this.#fetchData();
		if(!this.#fetched) return false;

		return this.#result.difference;
	}

	#getDayTime(date) {
		date = new Date(date);
		let time = ((
			date.getHours() * 60 +
			date.getMinutes()) * 60 + 
			date.getSeconds()) * 1000 +
			date.getMilliseconds();
		return time;
	}

	async getDayPeriod() {
		await this.#fetchData();
		if(!this.#fetched) return false;

		if(
			this.#result.sunset === undefined ||
			this.#result.sunrise === undefined
		) return false;

		let sunset = this.#getDayTime(this.#result.sunset);
		let sunrise = this.#getDayTime(this.#result.sunrise);
		let point = this.#getDayTime(this.#date);
		const diff_sunset = sunset - point;
		const diff_sunrise = sunrise - point;

		const margin = 1 * 60 * 60 * 1000;
		if(Math.abs(diff_sunset) < margin) return "Anochecer";
		if(Math.abs(diff_sunrise) < margin) return "Amanecer";
		if(point < sunrise || point > sunset) return "Noche";
		return "Día";
	}

	async convertedModify(element) {
		let converted = await this.getConverted();
		if(!converted) {
			element.html("Error al cargar datos");
			return;
		}

		let day = converted.getDate();
		day = day >= 10 ? day : ("0" + day);
		let month = converted.getMonth() + 1;
		month = month >= 10 ? month : ("0" + month);
		let year = converted.getFullYear();
		let hours = converted.getHours();
		let ampm = hours > 12 ? "pm" : "am";
		hours %= 12;
		hours = hours >= 10 ? hours : ("0" + hours);
		let minutes = converted.getMinutes();
		minutes = minutes >= 10 ? minutes : ("0" + minutes);
		let time = hours + ":" + minutes + ampm;

		element.html(`${day} del ${month}, ${year} a las ${time}`);
	}

	async dayPeriodModify(element) {
		let period = await this.getDayPeriod();
		if(!period) {
			element.html("Momento del día: No hay datos");
			return;
		}

		element.html(`Momento del día: ${period}`);
	}
}
