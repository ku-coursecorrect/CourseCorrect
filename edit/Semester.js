//Fall-Summer-Spring, year, and list of course codes placed in the semester
const MAX_HOURS = 19;
const SEASON_NAMES = ["Spring", "Summer", "Fall"];

/**
* @class
* @description Represents a single semester in a user's plan, storing its season, year, and the courses it contains
**/
class Semester {

	/**
		@param season {number} FALL, SUMMER, or SPRING constant
		@param year {number} year
		@param courses = {Course[]} An array of Course objects to initialize the semester with
		@post All parameters are assigned to their respective member variables
	*/
	constructor(season, year, courses) { // TODO reorder year and season
		this.season = season;
		this.year = year;
		this.courses = courses;
	}

	year_season() {
		return [this.year, this.season];
	}

	/**
		@return String from the string array Season names, 0=Spring, 1=Summer, 2=Fall
	*/
	season_name() {
		return SEASON_NAMES[this.season];
	}

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
