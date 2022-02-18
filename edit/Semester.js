//Fall-Summer-Spring, year, and list of course codes placed in the semester
const MAX_HOURS = 19;
const SEASON_NAMES = ["Spring", "Summer", "Fall"];

/**
* @class
* @description Represents a single semester in a user's plan, storing its season, year, and the courses it contains
**/
class Semester {

	/**
		@param id {number} Encodes the years and season (FALL, SUMMER, or SPRING constant) as year*3+season
		@param courses = {Course[]} An array of Course objects to initialize the semester with
		@post All parameters are assigned to their respective member variables
	*/
	constructor(id, courses = []) {
		this.id = id;
		this.courses = courses;
	}


	year() { return parseInt(this.id / 3); }
	season() { return this.id % 3; }
	season_name() { return SEASON_NAMES[this.season()]; }
	toString() { return this.season_name() + " " + this.year(); }

	/**
		@return the sum of the credit hours in the semester
	*/
	get_credit_hour() {
		return this.courses.reduce((sum, course) => sum + (course ? course.credit_hour : 0), 0);
	}

	/**
		@param column index, course object,
		@post semster_courses will add the course object at the column index
	*/
	add_course(col, course) {
		this.courses[col] = course;
	}

	/**
		@param course object
		@post removes the course object from the course array
	*/
	remove_course(course) {
		let courseIndex = this.courses.indexOf(course);
		if (courseIndex != undefined) {
			this.courses[courseIndex] = undefined;
			// Remove any trailing undefineds left in array (important for Plan.getLongest)
			for (let i = this.courses.length; i >= 0; i--) {
				if (this.courses[i] != undefined) return;
				this.courses.splice(i, 1);
			}
		}
	}
}
