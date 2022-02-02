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
		/*for (var i = 0; i < 4; i++) {
			//Makes 8 semester of fall/spring, flips between fall and spring
			//ONLY WOKRS IF YOU START AT FALL/SPRING
			this.semesters.push(new Semester(start_season, start_year, []));
			if (start_season == FALL) start_year++;
			this.semesters.push(new Semester(2-start_season, start_year, []));
			if (start_season == SPRING) start_year++;
		}*/

		this.plan_id = plan.plan_id;
		this.transfer_bank = plan.transfer_bank.map(course_id => course_id_to_object(courses, course_id));
		this.semesters = plan.semesters.map(semester => new Semester(
			semester.semester_season,
			semester.semester_year,
			semester.semester_courses.map(course_id => {
				/*if (Array.isArray(course_code)) { // custom course - recreate it
					let course = new Course(course_code[0], "Custom course", [], [], [1,1,1], course_code[1], true);
					COURSES.push(course);
					return course;
				}
				else*/ return course_id_to_object(courses, course_id)
			}),
		));

		// Populate course bank with courses that aren't in a semester or the transfer bank
		let placed_courses = this.transfer_bank.concat(...this.semesters.map(semester => semester.semester_courses));
		this.course_bank = courses.filter(course => !placed_courses.includes(course));
	}

	/**
		@return {string}, a JSON string containing the plan's name and semesters to send to the database
	*/
	save_json() {
		let plan = {
			"transfer_bank": this.transfer_bank.map(course => course.course_id),
			"semesters": this.semesters.map(semester => ({
				"semester_year": semester.semester_year,
				"semester_season": semester.semester_season,
				"semester_courses": semester.semester_courses.map(course => {
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
		return this.semesters[semester].semester_courses[col];
	}

	/**
		@param course_code {string} The code of the course to find
		@return {[number,number]} The semsester and column where the Course is at in the Plan
	*/
	find_course(course_code) {
		let coords;
		this.semesters.forEach((semester, y) => {
			semester.semester_courses.forEach((course, x) => {
				if (course != undefined && course_code == course.course_code) coords = [y, x];
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

	/**
		@param season {number} number 0-2, represents spring, summer, or fall
		@param year {number}
		@post creates a semester of season and year, which is added in the array
	*/
	add_semester(season, year) {
		let new_order = year*3 + season;
		
		// Edge case: Adding semester directly before first semester
		if (new_order < this.semesters[0].semester_year*3 + this.semesters[0].semester_season) {
			this.semesters.splice(0, 0, new Semester(season, year, []));
			return;
		}
		
		for (let i = 0; i < this.semesters.length; i++) {
			let old_order = this.semesters[i].semester_year*3 + this.semesters[i].semester_season;
			if (old_order+1 == new_order) {
				this.semesters.splice(i+1, 0, new Semester(season, year, []));
				return; // Important for preventing infinite loops
			}
		}
		
		// Add semester at end if location is not in middle
		this.semesters.splice(this.semesters.length, 0, new Semester(season, year, []));
	}

	/**
		@param season {number}
		@param year {number}
		@post semester at season and year index is deleted
	*/
	remove_semester(season, year) {
		// Find the requested semester object
		let i = this.semesters.findIndex(semester => season == semester.semester_season && year == semester.semester_year);

		// Prevent removing semesters containing courses
		if (this.semesters[i].semester_courses.find(course => course != undefined)) return;
		this.semesters.splice(i, 1);
	}

	/**
		@return {number} length of longest semester
	*/
	get_longest() {
		// Traverse through semesters, updating longest with the length of the longest semester found so far
		return this.semesters.reduce((longest, semester) => Math.max(semester.semester_courses.length, longest), MIN_COLS);
	}

	/**
		@post Generates the array of arrows for all prerequisite/corequisite relationships in the course grid
		@return {Arrow[]}, array of Arrow objects to render
	*/
	generate_arrows() {
		var arr_arrows = [];

		this.semesters.forEach((semester, y) => {
			semester.semester_courses.forEach((course, x) => {
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
