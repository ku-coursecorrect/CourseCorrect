//Major, list of semester objects, list of transfer/exempt courses
const SPRING = 0, SUMMER = 1, FALL = 2;
const MIN_COLS = 3; // Minimum number of columns to put courses in

/**
* @class
* @description Represents a user's plan, including their major, all semesters, and transfer credits
**/
class Plan {

	/**
		@param major_name {string} Major name
		@param start_semester {number} SPRING or FALL constant
		@param start_year {number} year
		@post All parameters are assigned to their respective member variables and the first 8 falls/springs after the start semester are added
	*/
	constructor(plan, courses) {
		this.plan_id = plan.plan_id;
		this.transfer_bank = plan.transfer_bank.map(course_id => course_id_to_object(courses, course_id));
		this.semesters = plan.semesters.map(semester => new Semester(
			semester.season,
			semester.year,
			semester.courses.map(course_id => {
				/*if (Array.isArray(course_code)) { // custom course - recreate it
					let course = new Course(course_code[0], "Custom course", [], [], [1,1,1], course_code[1], true);
					COURSES.push(course);
					return course;
				}
				else*/ return course_id_to_object(courses, course_id)
			}),
		));

		// Populate course bank with courses that aren't in a semester or the transfer bank
		let placed_courses = this.transfer_bank.concat(...this.semesters.map(semester => semester.courses));
		this.course_bank = courses.filter(course => !placed_courses.includes(course));
	}

	/**
		@return {string}, a JSON string containing the plan's name and semesters to send to the database
	*/
	save_json() {
		let plan = {
			"transfer_bank": this.transfer_bank.map(course => course.course_id),
			"semesters": this.semesters.map(semester => ({
				"year": semester.year,
				"season": semester.season,
				"courses": semester.courses.map(course => {
					if (course == undefined) return "";
					//else if (course.is_custom) return [course.course_code, course.credit_hour]; // TODO: handle custom courses
					else return course.course_id;
				}),
			})),
		}
		return JSON.stringify(plan);
	}

	/**
		@param semester {number}, semester index for array
		@param column {number}, column index for array
		@return course at column in the semsester
	*/
	get_course(semester, col) {
		return this.semesters[semester].courses[col];
	}

	/**
		@param course_id {string} The id of the course to find
		@return {[number,number]} The semsester and column where the Course is at in the Plan
	*/
	find_course(course_id) {
		let coords;
		this.semesters.forEach((semester, y) => {
			semester.courses.forEach((course, x) => {
				if (course != undefined && course_id == course.course_id) coords = [y, x];
			});
		});
		return coords;
	}

	/**
		@param semester {number} Semester index for the array
		@param column {number} Column index for the array
		@param course {Course} Course to add in the array
		@post Adds Course object at specified column in specified semester of plan
	*/
	add_course(semester, col, course) {
		this.semesters[semester].add_course(col, course);
	}

	/**
		@param course {Course} Course object to remove
		@post Finds and deletes course object from the wherever it is in the plan
	*/
	remove_course(course) {
		//check course and transfer banks
		for (let bank of [this.course_bank, this.transfer_bank]) {
			for (let i = 0; i < bank.length; i++) {
				if (course == bank[i]) {
					bank.splice(i, 1);
					return;
				}
			}
		}
		// Not found above - must be in semester grid
		for (let semester of this.semesters) {
			semester.remove_course(course);
		}
	}


	find_semester([year, season]) {
		return this.semesters.find(semester => semester.year == year && semester.season == season);
	}

	/**
	    @param year {number}
		@param season {number} number 0-2, represents spring, summer, or fall
		@post creates a semester of season and year, which is added in the array
	*/
	add_semester(year, season) {
		this.semesters.push(new Semester(season, year, []));
		this.semesters.sort((sem1, sem2) => (sem1.year*3+sem1.season) - (sem2.year*3+sem2.season));
	}

	/**
	    @param year {number}
		@param season {number}
		@post semester at season and year index is deleted
	*/
	remove_semester(year, season) {
		// Find the requested semester object
		let i = this.semesters.findIndex(semester => season == semester.season && year == semester.year);

		// Prevent removing semesters containing courses
		if (this.semesters[i].courses.find(course => course != undefined)) return;
		this.semesters.splice(i, 1);
	}

	/**
		@return {number} length of longest semester
	*/
	get_longest() {
		// Traverse through semesters, updating longest with the length of the longest semester found so far
		return this.semesters.reduce((longest, semester) => Math.max(semester.courses.length, longest), MIN_COLS);
	}

	/**
		@post Generates the array of arrows for all prerequisite/corequisite relationships in the course grid
		@return {Arrow[]}, array of Arrow objects to render
	*/
	generate_arrows() {
		var arr_arrows = [];

		this.semesters.forEach((semester, y) => {
			semester.courses.forEach((course, x) => {
				if (course != undefined) {
					for (let reqs of [course.prereq, course.coreq]) {
						for (let req of reqs) {
							let coord_req = this.find_course(req);
							if (coord_req != undefined) {
								arr_arrows.push(new Arrow(coord_req[1], coord_req[0], x, y, reqs == course.coreq));
							}
						}
					}
				}
			});
		});

		return arr_arrows;
	}
}
