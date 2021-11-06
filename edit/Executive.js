const BANK_COLS = 3;

/**
* @class
* @description Manages user interaction, updating the plan and elements on the page as required
**/
class Executive {

	/**
	* @post Dropdowns are populated and event listeners are set up on elements of the page the user can interact with
	**/
	constructor(test) {
		// used for test suite
		if (test) {
			return;
		}
		this.arrowRender = new ArrowRender();

		// Add tooltips to courses
		$('#redips-drag').tooltip({selector: '[data-toggle="tooltip"]'})

		// Populate options for major
		for (let major of MAJORS) {
			this.makeElement("option", "majorSelect", major.major_name, major.major_name);
		}

		// Populate options for starting semester
		let thisYear = new Date().getFullYear();
		for (let year = thisYear; year >= thisYear-3; year--) {
			for (let season of [FALL, SPRING]) {
				this.makeElement("option", "startSemesterSelect", "Start in " + SEASON_NAMES[season] + " " + year, year + "-" + season);
			}
		}

		// Initialize plan when done is clicked (arrow function used to preserve this)
		document.getElementById("done").addEventListener("click", () => this.initPlan());

		// Populate list of saved plans to load
		for (let i = 0; i < localStorage.length; i++) {
			let key = localStorage.key(i);
			if (key.startsWith("gpg-1-")) {
				let plan = localStorage.getItem(key);
				try {
					plan = JSON.parse(plan);
					let date = new Date(plan.timestamp);
					// Get the date the plan was last modified in YYYY-MM-DD format, correcting for time zone
					let text = new Date(plan.timestamp - new Date().getTimezoneOffset()*60000).toISOString().split("T")[0] + ": " + key.substr(6);
					this.makeElement("option", "planSelect", text, key);
				}
				catch (e) {
					// Skip plans with formatting issues
					console.log(e);
				}
			}
		}

		// Load existing plan on click
		document.getElementById("load-plan").addEventListener("click", () => {
			let key = document.getElementById("planSelect").value;
			if (key == "-1") return; // Do nothing if dropdown not selected
			// Read plan string before init. This is important when loading the autosave plan.
			let plan_string = localStorage.getItem(key);
			this.initPlan(); // Default plan will be overwritten if parse succeeds
			this.plan.string_to_plan(plan_string);
			this.update();
			// Substr to remove the gpg-1-
			document.getElementById("save-name").value = document.getElementById("planSelect").value.substr(6);
		});

		// Import plan
		document.getElementById("import-plan").addEventListener("click", () => {
			let plan_string = document.getElementById("plan-to-import").value;
			this.initPlan(); // Default plan will be overwritten if parse succeeds
			this.plan.string_to_plan(plan_string);
			this.update();
		});

		// Delete saved plan
		document.getElementById("delete-plan").addEventListener("click", () => {
			let key = document.getElementById("planSelect").value;
			if (key == "-1") return; // Do nothing if dropdown not selected
			localStorage.removeItem(key);
			// Remove plan from dropdown
			document.getElementById("planSelect").remove(document.getElementById("planSelect").selectedIndex);
			document.getElementById("planSelect").selectedIndex = 0;
		});

		// Plan save button
		document.getElementById("save-button").addEventListener("click", () => {
			let name = document.getElementById("save-name").value;
			// Default name e.g. Computer Science Fall 2018
			if (!name) name = this.plan.major.major_name + " " + this.plan.semesters[0].season_name() + " " + this.plan.semesters[0].semester_year;
			name = name.replace(/[^\w\s]/g, ""); // Remove special characters from name
			this.savePlan(name);
		});

		// Plan export button
		document.getElementById("export-button").addEventListener("click", () => {
			// Copy plan to clipboard
			let textarea = document.createElement("textarea");
			textarea.style.style = "position: absolute; left: -999px; top: -999px"; // display none prevents this from working
			document.body.appendChild(textarea);
			textarea.value = this.plan.plan_to_string();
			textarea.select();
			document.execCommand("copy");
			document.body.removeChild(textarea);

			// Display alert that auto-closes
			document.getElementById("plan-exported").style.display = "";
			window.setTimeout(() => document.getElementById("plan-exported").style.display = "none", 5000);
		});

		// Initialize drag-and-drop to move courses
		REDIPS.drag.dropMode = "single";
		REDIPS.drag.event.clicked = targetCell => {
			// Remove tooltip while dragging
			delete targetCell.firstElementChild.dataset.toggle;
			$(targetCell.firstElementChild).tooltip("dispose");
		};
		REDIPS.drag.event.dropped = targetCell => {
			// Clear all notifications
			for (let id of ["notifications", "print-notifications"]) {
				let list = document.getElementById(id);
				while (list.firstChild) list.removeChild(list.firstChild);
			}

			// Remove tutorial if present
			$(".tutorial").remove();

			let course = this.plan.course_code_to_object(targetCell.firstElementChild.dataset["course"]);
			this.plan.remove_course(course); // Remove course from wherever it is
			if (targetCell.dataset["bank"] == "course") {
				this.plan.course_bank.push(course);
			}
			else if (targetCell.dataset["bank"] == "transfer") {
				this.plan.transfer_bank.push(course);
			}
			else {
				this.plan.add_course(targetCell.dataset["y"], targetCell.dataset["x"], course);
			}
			this.update();
		};

		// Adding a semester
		document.getElementById('add-semester-btn').addEventListener('click', () => {
			let semester = document.getElementById("addSemesterSelect").value;
			if (semester == "-1") return; // Do nothing if dropdown not selected
			let [year, season] = semester.split('-').map(Number);

			// Remove semester from dropdown
			document.getElementById("addSemesterSelect").remove(document.getElementById("addSemesterSelect").selectedIndex);
			document.getElementById("addSemesterSelect").selectedIndex = 0;

			this.plan.add_semester(season, year);
			this.update();
		});

		// Adding a custom course
		document.getElementById("course_add_submit").addEventListener("click", () => {
			let t_course_code = document.getElementById("course_code").value;
			let t_credit_hours = parseInt(document.getElementById("credit_hours").value);
			if (t_course_code == "" || isNaN(t_credit_hours)) return; // Both inputs not filled out
			if (this.plan.course_code_to_object(t_course_code) == undefined) {
				let temp = new Course(t_course_code, "Custom course", [], [], [1,1,1], t_credit_hours, true);
				COURSES.push(temp);
				this.plan.course_bank.push(temp);
				this.update();
			}
			document.getElementById("course_code").value = "";
			document.getElementById("credit_hours").value = "";
		});

		// Test plan
		//this.createTestPlan();
	}

	/**
	* @pre The values of the starting semester and major dropdowns are valid
	* @post The welcome screen is hidden and an 8-semester plan is initialized with all courses for the selected major in the course bank
	**/
	initPlan() {
		let [year, season] = document.getElementById("startSemesterSelect").value.split('-').map(Number);
		let major = document.getElementById("majorSelect").value;
		document.getElementById("showMajor").innerHTML = "Major: " + major;

		document.getElementById("welcome").style.display = "none";
		document.getElementById("add-semester").style.display = "";
		document.getElementById("add_extra_course_box").style.display = "";
		document.getElementById("save-container").style.display = "";
		this.plan = new Plan(major, season, year);
		this.update();
		// Add help text in the first cell
		document.getElementById("course-grid").rows[0].cells[1].innerHTML = "<div class='tutorial'>Drag-and-drop a course here..</div>";

		// Set up adding semesters - add the summers between the automatic semesters
		for (let tmpYear = year; tmpYear < year+4; tmpYear++) {
			this.makeElement("option", "addSemesterSelect", SEASON_NAMES[SUMMER] + " " + tmpYear, tmpYear + "-" + SUMMER);
		}

		// Option to add the next few semsters
		year += season == FALL ? 4 : 3;
		season = 2-season;
		for (let semester = 1; semester <= 9; semester++) {
			season++;
			if (season >= 3) {
				season -= 3;
				year++;
			}
			this.makeElement("option", "addSemesterSelect", SEASON_NAMES[season] + " " + year, year + "-" + season);
		}
	}

	/**
	* @post All aspects of the plan are updated: Course locations, arrows, credit hours per semester, errors/warnings, etc.
	**/
	update() {
		// Update course bank and transfer credits
		this.renderBank("course-bank", this.plan.course_bank);
		this.renderBank("transfer-bank", this.plan.transfer_bank);
		document.getElementById("print-course-bank").innerText = this.plan.course_bank.map(course => course.course_code).join(", ") || "None";
		document.getElementById("print-transfer-bank").innerText = this.plan.transfer_bank.map(course => course.course_code).join(", ") || "None";

		// Update main semester grid
		this.renderCourseGrid(); // Must call before renderArrows
		let arrows = this.plan.generate_arrows();
		this.arrowRender.renderArrows(arrows);
		REDIPS.drag.init(); // Updates which elements have drag-and-drop

		// Update the credit hour displays and the Upper level eligibility
		for (let semester of this.plan.semesters) {
			let credit_hours = semester.get_credit_hour();
			document.getElementById("ch" + semester.semester_year + "-" + semester.semester_season).innerText = credit_hours + " credit hours";
			if (credit_hours > MAX_HOURS) { // Add excessive hour warnings
				this.add_error("EXCESS HOURS: " + semester.season_name() + " " + semester.semester_year + ": You are taking more than " + MAX_HOURS +
					" credit hours. You will need to fill out a waiver.\n");
				document.getElementById("ch" + semester.semester_year + "-" + semester.semester_season).classList.add("error");
			}
		}

		// Check for invalid placements
		for (let arrow of arrows) {
			if (!arrow.fromSide && arrow.yIn >= arrow.yOut) { // Invalid prerequisite
				this.add_error("INVALID COURSE: " + this.plan.get_course(arrow.yIn, arrow.xIn).course_code
					+ " is a prerequisite of " + this.plan.get_course(arrow.yOut, arrow.xOut).course_code + "\n");
				// Add error class to course. +1 on the x is to account for the semester name column.
				document.getElementById("course-grid").rows[arrow.yOut].cells[arrow.xOut+1].firstElementChild.classList.add("error");
				// TODO: Make arrow red (will require moving renderArrows call to after this loop)
			}
			else if (arrow.fromSide && arrow.yIn > arrow.yOut) { // Invalid corequisite
				this.add_error("INVALID COURSE: " + this.plan.get_course(arrow.yIn, arrow.xIn).course_code
					+ " is a corequisite of " + this.plan.get_course(arrow.yOut, arrow.xOut).course_code + "\n");
				// Add error class to course. +1 on the x is to account for the semester name column.
				document.getElementById("course-grid").rows[arrow.yOut].cells[arrow.xOut+1].firstElementChild.classList.add("error");
			}
		}
		this.checkULE();

		// Autosave plan
		this.savePlan("autosave");
	}

	/**
	* @param html_id {string} The id of the table element to render the courses to
	* @param arrCourse {Course[]} An array of course objects to render to the table
	* @post The table referred to in html_id is cleared, resized, and populated with drag-and-droppable course elements
	**/
	renderBank(html_id, arrCourse) {
		let grid = document.getElementById(html_id);
		while (grid.firstChild) grid.removeChild(grid.firstChild); // Clear bank
		let tr;
		let numCoursesInCurrentRow = BANK_COLS;
		// At least one more cell than the number of courses, then round up to multiple of 3
		let totalCells = Math.ceil((arrCourse.length+1)/BANK_COLS)*BANK_COLS;
		for (let i = 0; i < totalCells; i++) {
			if (numCoursesInCurrentRow == BANK_COLS) {
				tr = document.createElement("tr");
				grid.appendChild(tr);
				numCoursesInCurrentRow = 0;
			}
			let td = document.createElement("td");
			td.dataset["bank"] = (html_id == "course-bank") ? "course" : "transfer";
			if (arrCourse[i]) td.innerHTML = arrCourse[i].to_html();
			tr.appendChild(td);
			numCoursesInCurrentRow++;
		}
	}

	/**
	* @post The #course-grid table is updated with rows for each semester in this.plan containing drag-and-droppable courses in the correct locations, credit hours per semester, delete buttons, etc.
	**/
	renderCourseGrid() {
		let grid = document.getElementById("course-grid");
		while (grid.firstChild) grid.removeChild(grid.firstChild); // Clear grid

		let cols = this.plan.get_longest() + 1; // +1 leaves an empty column to add another course to a semester
		for (let i = 0; i < this.plan.semesters.length; i++) {
			let semester = this.plan.semesters[i];
			let tr = document.createElement("tr");

			let th = document.createElement("th");
			th.className = "redips-mark";
			th.innerHTML = semester.semester_year + " " + semester.season_name() + "<br><span class='ch' id='ch"+semester.semester_year+"-"+semester.semester_season+"'>0 credit hours</span>";
			tr.appendChild(th);

			// Delete button
			if (semester.semester_courses.length == 0) {
				let dele = document.createElement("button");
				dele.className = "btn btn-sm btn-danger delete-semester";
				dele.innerHTML = '<i class="fa fa-trash"></i>';
				dele.addEventListener("click", e => {
					this.plan.remove_semester(semester.semester_season, semester.semester_year);
					// Add semester to dropdown so it can be re-added
					this.makeElement("option", "addSemesterSelect", semester.season_name() + " " + semester.semester_year, semester.semester_year + "-" + semester.semester_season);
					this.update();
				});
				th.appendChild(dele);
			}

			for (let j = 0; j < cols; j++) {
				let td = document.createElement("td"); // Create a table cell.
				if (semester.semester_courses[j] != undefined) {
					let course = semester.semester_courses[j];
					td.innerHTML = course.to_html(); // Formats the contents of the table cell.

					// If the course that is being loaded into the table cell is not offered in the current semester's season.
					if (course.course_semester[semester.semester_season] != 1) { 
						td.firstElementChild.classList.add("error"); // Stylize the cell to be red
						this.add_error(course.course_code + " is not offered in the " + semester.season_name()); // Display an error message
					}
				}
				td.dataset["x"] = j;
				td.dataset["y"] = i;
				tr.appendChild(td);

			}

			grid.appendChild(tr);
		}

		this.arrowRender.resize(this.plan.semesters.length, cols);
	}

	/**
	* @brief checks for upper level eligibility
	* @post adds a notification if a course needs upper level eligibility
	* @returns a bool of whether there is upper level eligibility
	**/
	checkULE() {
		let ule_req_count = 0;
		for (let courses of this.plan.major.ule) {
			if (this.plan.transfer_bank.find(course => {if (course != undefined) if (course.course_code == courses) return course;}) != undefined) {
				ule_req_count++;
			}
		}
		for (let semester of this.plan.semesters) {
			if (ule_req_count < this.plan.major.ule.length) {
				if (semester.semester_courses.length > 0) {
					for (let courses of semester.semester_courses) {
						if (courses != undefined) {
							if (this.plan.major.ule.find(course => {if (course != undefined) if (course == courses.course_code) return course;}) == undefined) {
								let code = courses.course_code.split(" ");
								if ((code[0] == "EECS" && parseInt(code[1]) > 300) || (code[0] == "Sen")) {
									if (ULE_EXCECPTIONS.find(course => course == courses.course_code) == undefined) {
										this.add_error("WAIVER REQUIRED: " + courses.course_code + " needs Upper Level Eligibility. \n");
										let coord = this.plan.find_course(courses.course_code);
										document.getElementById("course-grid").rows[coord[0]].cells[coord[1]+1].firstElementChild.classList.add("warning");
									}
								}
							} else {
								ule_req_count++;
							}
						}
					}
				}
			} else {
				return true;
			}
		}
		return false;
	}

	/**
	* @param msg {string} The error message about the plan to display to the user
	* @post The message is added to the elements on the page and print layoutt
	**/
	add_error(msg) {
		for (let id of ["notifications", "print-notifications"]) {
			this.makeElement("li", id, msg);
		}
	}

	/**
	* @param name {string} The name the saved plan will have
	* @post The current this.plan is saved to the browser's local storage with a name prefix specific to this applicaion
	**/
	savePlan(name) {
		// 1 is for version number
		localStorage.setItem("gpg-1-" + name, this.plan.plan_to_string());
	}

	/**
	* @param name {string} The name of the plan saved in the user's browser (must exist)
	* @post The plan matching the name is retrieved from the browser's local storage and used to populate this.plan
	**/
	loadPlan(name) {
		this.plan.string_to_plan(localStorage.getItem("gpg-1-" + name));
	}

	/**
	* @brief This is a helper method used to reduce the repetitiveness of this code as creating elements is done in several places
	* @param type {string} The type of the DOM element to create
	* @param parentId {string} The HTML id of the existing DOM element to append the new element to (optional)
	* @param text {string} The contents of the element (optional)
	* @param value {string} The value of the element (optional)
	**/
	makeElement(type, parentId, text, value) {
		let el = document.createElement(type);
		if (value) el.value = value;
		if (text) el.appendChild(document.createTextNode(text));
		if (parentId) document.getElementById(parentId).appendChild(el);
		return el;
	}

	/**
	* @post An example plan is created to test arrow rendering and other aspects of the code
	**/
	createTestPlan() {
		document.getElementById("welcome").style.display = "none";
		this.plan = new Plan("Computer Science", FALL, 2018);
		this.plan.add_course(0, 1, this.plan.course_code_to_object("EECS 168"));
		this.plan.add_course(0, 2, this.plan.course_code_to_object("EECS 140"));
		this.plan.add_course(1, 1, this.plan.course_code_to_object("MATH 125"));
		this.plan.add_course(1, 3, this.plan.course_code_to_object("GE 2.2"));
		this.plan.add_course(2, 0, this.plan.course_code_to_object("EECS 268"));
		this.plan.add_course(2, 1, this.plan.course_code_to_object("PHSX 210"));
		this.plan.add_course(2, 2, this.plan.course_code_to_object("EECS 388"));
		this.plan.add_course(2, 3, this.plan.course_code_to_object("PHSX 216"));
		this.update();
	}
}
