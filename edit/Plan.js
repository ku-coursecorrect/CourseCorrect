//Major, list of semester objects, list of transfer/exempt courses
const SPRING = 0, SUMMER = 1, FALL = 2;
const MIN_COLS = 5; // Minimum number of columns to put courses in

/**
* @class
* @description Represents a user's plan, including their major, all semesters, and transfer credits
**/
class Plan {
	/**
		@param plan {string} JSON plan data, also includes ID, title, status, etc.
		@param courses {string} JSON list of courses required for the plan's degree
	*/
	constructor(plan, courses) {
		this.plan_id = plan.plan_id;
		this.transfer_bank = plan.transfer_bank.map(course_id => course_id_to_object(courses, course_id));
		this.semesters = plan.semesters.map(semester => new Semester(
			semester.id,
			semester.courses.map(course => {
				if (!course) return;
				if (typeof course == "object") { // custom course - recreate it
					let course_obj = new Course("custom_" + course.course_code, course.course_code, "Custom course", [], [], [1,1,1], course.credit_hours);
					courses.push(course_obj);
					return course_obj;
				}
				else return course_id_to_object(courses, course)
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
				"id": semester.id,
				"courses": semester.courses.map(course => {
					if (course == undefined) return ""; // Empty slot in semester
					if (!(course.course_id > 0)) return {"course_code": course.course_code, "credit_hours": course.credit_hour}; // Custom course
					return course.course_id;
				}),
			})),
			"notes": document.getElementById("notes").value,
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


	find_semester(id) {
		return this.semesters.find(semester => semester.id == id);
	}

	/**
	    @param id {number}
		@post creates a semester of season and year, which is added in the array
	*/
	add_semester(id) {
		id = parseInt(id);
		this.semesters.push(new Semester(id, []));
		this.semesters.sort((sem1, sem2) => sem1.id - sem2.id);
	}

	/**
	    @param id {number}
		@post semester at season and year index is deleted
	*/
	remove_semester(id) {
		// Find the requested semester object
		let i = this.semesters.findIndex(semester => id == semester.id);

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
