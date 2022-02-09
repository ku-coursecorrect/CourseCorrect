const BANK_COLS = 3;

/**
* @class
* @description Manages user interaction, updating the plan and elements on the page as required
**/
class Executive {

	/**
	* @post Dropdowns are populated and event listeners are set up on elements of the page the user can interact with
	**/
	constructor(courses = null, plan = null, test = false) {
		if (test) return; // used for test suite (which is currently broken due to the paramters changing - TODO)
		this.arrowRender = new ArrowRender();

		this.courses = courses.map(course => new Course(course.course_id,
														course.course_code, 
														course.title, 
														course.prereq,
														course.coreq,
														[course.f_spring, course.f_summer, course.f_fall], 
														course.max_hours, 
														false));

		// Load plan
		document.getElementById("plan_title").value = plan.plan_title;
		document.getElementById("degree_title").innerText = plan.degree_major + " " + plan.degree_year;
		this.plan = new Plan(plan, this.courses);

		this.update();

		// Add help text in the first cell
		if (this.plan.semesters.length >= 1 && this.plan.semesters[0].courses.length < 1) {
			document.getElementById("course-grid").rows[0].cells[1].innerHTML = "<div class='tutorial'>Drag-and-drop a course here..</div>";
		}

		// The rest of this sets up event listeners for user interactions

		// Plan save button
		if (this.plan.plan_id) {
			document.getElementById("save-button").addEventListener("click", () => {
				let data = new FormData();
				data.append("plan_id", this.plan.plan_id);
				data.append("plan_title", document.getElementById("plan_title").value);
				data.append("json", this.plan.save_json());

				fetch("save.php", {"method": "POST", "body": data}).then(response => {
					if (response.ok) {
						console.log(response);
						this.displayAlert("success", "Plan saved", 5000);
					}
					else {
						console.error(response);
						this.displayAlert("danger", "Error saving plan", 5000);
					}
					response.text().then(text => console.log(text));
				});
			});
		}
		else {
			document.getElementById("save-container").style.display = "none";
		}

		// Add tooltips to courses
		$('#redips-drag').tooltip({selector: '[data-toggle="tooltip"]'})

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

			let course = course_id_to_object(this.courses, targetCell.firstElementChild.dataset["course"]);
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
			this.plan.add_semester(year, season);
			this.update();
		});

		// Adding a custom course
		document.getElementById("course_add_submit").addEventListener("click", () => {
			let t_course_code = document.getElementById("course_code").value;
			let t_credit_hours = parseInt(document.getElementById("credit_hours").value);
			if (t_course_code == "" || isNaN(t_credit_hours)) return; // Both inputs not filled out

			let course = new Course("custom_" + t_course_code, t_course_code, "Custom course", [], [], [1,1,1], t_credit_hours, true);
			this.courses.push(course);
			this.plan.course_bank.push(course);
			this.update();

			document.getElementById("course_code").value = "";
			document.getElementById("credit_hours").value = "";
		});
	}

	alertCount = 0;
	displayAlert(type, message, timeout) {
		let alertId = "alert-" + this.alertCount;
		document.getElementById("alert_holder").innerHTML += `<div id="${alertId}" class="alert alert-${type} alert-dismissable mb-4 fade show" id="alert">
			<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
			${message}
		</div>`;
		if (timeout) setTimeout(() => $("#" + alertId).alert("close"), timeout);
		this.alertCount++;
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

		// Clear add semester dropdown
		let addSemesterSelect = document.getElementById("addSemesterSelect");
		while (addSemesterSelect.firstChild) addSemesterSelect.removeChild(addSemesterSelect.firstChild);

		if (this.plan.semesters.length < 1) {
			// TODO - should probably just not let people remove every semester
		}

		// TODO: Change all this to use a Semester.toNum method which is year*3+season so math works easily

		// Increment a [year, season] semester representation
		const incSemester = ([year, season], inc = 1) => { 
			season += inc; 
			while (season >= 3) { season -= 3; year++; } 
			while (season < 0) { season += 3; year--; }
			return [year, season];
		};

		// Populate add semester dropdown with three semesters before and after current plan and any removed ones in-between
		for (
			let semester = incSemester(this.plan.semesters[0].year_season(), -3);
			semester < incSemester(this.plan.semesters[this.plan.semesters.length-1].year_season(), 4);
			semester = incSemester(semester)
		) {
			console.log(this.plan.find_semester(semester), semester);
			if (!this.plan.find_semester(semester)) {
				let [year, season] = semester;
				this.makeElement("option", "addSemesterSelect", SEASON_NAMES[season] + " " + year, year + "-" + season);
			}
		}

		// Update the credit hour displays and the Upper level eligibility
		for (let semester of this.plan.semesters) {
			let credit_hours = semester.get_credit_hour();
			document.getElementById("ch" + semester.year + "-" + semester.season).innerText = credit_hours + " credit hours";
			if (credit_hours > MAX_HOURS) { // Add excessive hour warnings
				this.add_error("EXCESS HOURS: " + semester.season_name() + " " + semester.year + ": You are taking more than " + MAX_HOURS +
					" credit hours. You will need to fill out a waiver.\n");
				document.getElementById("ch" + semester.year + "-" + semester.season).classList.add("error");
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

		// TODO: reimplement ULE
		//this.checkULE();

		// Autosave plan TODO
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
			th.innerHTML = semester.year + " " + semester.season_name() + "<br><span class='ch' id='ch"+semester.year+"-"+semester.season+"'>0 credit hours</span>";
			tr.appendChild(th);

			// Delete button
			if (semester.courses.length == 0) {
				let dele = document.createElement("button");
				dele.className = "btn btn-sm btn-danger delete-semester";
				dele.innerHTML = '<i class="fa fa-trash"></i>';
				dele.addEventListener("click", e => {
					this.plan.remove_semester(semester.year, semester.season);
					this.update();
				});
				th.appendChild(dele);
			}

			for (let j = 0; j < cols; j++) {
				let td = document.createElement("td"); // Create a table cell.
				if (semester.courses[j] != undefined) {
					let course = semester.courses[j];
					td.innerHTML = course.to_html(); // Formats the contents of the table cell.

					// If the course that is being loaded into the table cell is not offered in the current semester's season.
					if (course.course_semester[semester.season] != 1) { 
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
				if (semester.courses.length > 0) {
					for (let courses of semester.courses) {
						if (courses != undefined) {
							if (this.plan.major.ule.find(course => {if (course != undefined) if (course == courses.course_code) return course;}) == undefined) {
								let code = courses.course_code.split(" ");
								if ((code[0] == "EECS" && parseInt(code[1]) > 300) || (code[0] == "Sen")) {
									if (ULE_EXCECPTIONS.find(course => course == courses.course_code) == undefined) {
										this.add_error("WAIVER REQUIRED: " + courses.course_code + " needs Upper Level Eligibility. \n");
										let coord = this.plan.find_course(courses.course_id);
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
}
