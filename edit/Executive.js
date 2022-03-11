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
														course.hours,
														course.f_ule,
														course.description));

		// Load plan
		document.getElementById("plan_title").value = plan.plan_title;
		this.plan = new Plan(plan, this.courses);

		this.update(true);

		// Add help text in the first cell
		if (this.plan.semesters.length >= 1 && this.plan.semesters[0].courses.length < 1) {
			document.getElementById("course-grid").rows[0].cells[1].innerHTML = "<div class='tutorial'>Drag-and-drop a course here..</div>";
		}

		// The rest of this sets up event listeners for user interactions

		// Setup plan save button if logged in
		if (this.plan.plan_id) {
			document.getElementById("save-button").addEventListener("click", () => {
				let data = new FormData();
				data.append("plan_id", this.plan.plan_id);
				data.append("plan_title", document.getElementById("plan_title").value);
				data.append("plan_status", this.plan_status);
				data.append("json", this.plan.save_json());

				fetch("save.php", {"method": "POST", "body": data}).then(response => {
					if (response.ok) {
						console.log(response);
						this.displayAlert("success", "Plan saved", 5000);
						document.getElementById("save-button").disabled = true;
					}
					else {
						console.error(response);
						this.displayAlert("danger", "Error saving plan", 5000);
					}
					response.text().then(text => console.log(text));
				});
			});

			// Unsaved plan warning
			window.addEventListener("beforeunload", e => {
				if (document.getElementById("save-button").disabled == false) {
					var msg = "Warning: Your plan has unsaved changes. Continue?";
					e.returnValue = msg;
					return msg;
				}
			});
		}
		else { // Guest mode - no save button
			document.getElementById("save-container").style.display = "none";
		}

		// Add tooltips to courses - removed because it makes it hard to see the arrows which get highlighted on hover
		//$('#redips-drag').tooltip({selector: '[data-toggle="tooltip"]'})

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
			let id = document.getElementById("addSemesterSelect").value;
			if (id == "-1") return; // Do nothing if dropdown not selected
			this.plan.add_semester(id);
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

		// Deleting a custom course
		document.getElementById("course-delete").addEventListener("click", () => {
			let course_id = document.getElementById("course-delete").dataset.course;

			// Loop through every place that can have courses: semesters, course bank, and transfer credits
			for (let semester of [...this.plan.semesters.map(semester => semester.courses), this.plan.course_bank, this.plan.transfer_bank]) {
				// See if the semester contains the course
				let index = semester.findIndex(course => course && course.course_id == course_id);
				// Delete it if so
				if (index > -1) semester[index] = undefined;
			}

			// Delete the course from the courses list
			this.courses.splice(this.courses.findIndex(course => course && course.course_id == course_id), 1);

			// Redraw plan
			this.update();

			// Clear the course info box
			document.getElementById("course-title").innerText = "Course info";
			document.getElementById("course-subtitle").innerText = "";
			document.getElementById("course-description").innerText = "Click on a course to display information and options here.";
			document.getElementById("course-delete").style.display = "none";
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
	update(firstLoad = false) {
		if (!firstLoad) {
			document.getElementById("save-button").disabled = false;
		}

		this.plan_status = 4; // 4 = complete (any errors/warnings that occur alter this)
		if (this.plan.course_bank.length > 0) this.plan.status = 1; // 1 = incomplete

		// Update course bank and transfer credits
		this.plan.course_bank.sort((a, b) => (a.course_code > b.course_code ? 1 : -1));
		this.plan.transfer_bank.sort((a, b) => (a.course_code > b.course_code ? 1 : -1));
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

		// Populate add semester dropdown with three semesters before and after current plan and any removed ones in-between
		for (let id = this.plan.semesters[0].id - 3; id < this.plan.semesters[this.plan.semesters.length-1].id + 4; id++) {
			if (!this.plan.find_semester(id)) {
				this.makeElement("option", "addSemesterSelect", (new Semester(id)).toString(), id);
			}
		}
		// Select the next semester after the end of the plan by default
		document.getElementById("addSemesterSelect").value = this.plan.semesters[this.plan.semesters.length-1].id + 1;

		// Update the credit hour displays and the Upper level eligibility
		for (let semester of this.plan.semesters) {
			let credit_hours = semester.get_credit_hour();
			document.getElementById("ch" + semester.id).innerText = credit_hours + " credit hours";
			if (credit_hours > MAX_HOURS) { // Add excessive hour warnings
				this.add_error(`<b>EXCESS HOURS:</b> More than ${MAX_HOURS} credit hours in ${semester} (waiver required)`, "warning");
				document.getElementById("ch" + semester.id).classList.add("error");
			}
		}

		// Check for invalid placements
		for (let arrow of arrows) {
			if (!arrow.fromSide && arrow.yIn >= arrow.yOut) { // Invalid prerequisite
				this.add_error("<b>PREREQUISITE ERROR:</b> " + this.plan.get_course(arrow.yIn, arrow.xIn).course_code
					+ " is a prerequisite of " + this.plan.get_course(arrow.yOut, arrow.xOut).course_code + "\n");
				// Add error class to course. +1 on the x is to account for the semester name column.
				document.getElementById("course-grid").rows[arrow.yOut].cells[arrow.xOut+1].firstElementChild.classList.add("error");
				// TODO: Make arrow red (will require moving renderArrows call to after this loop)
			}
			else if (arrow.fromSide && arrow.yIn > arrow.yOut) { // Invalid corequisite
				this.add_error("<b>COREQUISITE ERROR:</b> " + this.plan.get_course(arrow.yIn, arrow.xIn).course_code
					+ " is a corequisite of " + this.plan.get_course(arrow.yOut, arrow.xOut).course_code + "\n");
				// Add error class to course. +1 on the x is to account for the semester name column.
				document.getElementById("course-grid").rows[arrow.yOut].cells[arrow.xOut+1].firstElementChild.classList.add("error");
			}
		}

		// Find the semester the last ULE.Requirement course is taken
		let lastUleRequirementSemesterId = 0;
		for (let semester of this.plan.semesters) {
			for (let course of semester.courses) {
				if (course?.ule == ULE.Requirement) {
					lastUleRequirementSemesterId = semester.id;
				}
			}
		}
		for (let course of this.plan.course_bank) {
			if (course.ule == ULE.Requirement) { // Course in course bank still
				lastUleRequirementSemesterId = 99999; // Hacky way to make it in the future
			}
		}

		// Check for any ULE errors
		for (let semester of this.plan.semesters) {
			// Semester ULE gets completed
			if (semester.id == lastUleRequirementSemesterId) {
				for (let course of semester.courses) {
					if (course?.ule == ULE.RequiresCompletion) {
						this.add_error(`<b>UPPER-LEVEL ELIGIBILITY:</b> ${course.course_code} placed before ULE requirements completed (waiver required)`, "warning");
						document.querySelector(`[data-course="${course.course_id}"`).classList.add("warning");
					}
				}
				break;
			}
			// Semesters before ULE is complete
			for (let course of semester.courses) {
				if (course?.ule == ULE.LastSemesterException || course?.ule == ULE.RequiresCompletion) {
					this.add_error(`<b>UPPER-LEVEL ELIGIBILITY:</b> ${course.course_code} placed before ULE requirements completed (waiver required)`, "warning");
					document.querySelector(`[data-course="${course.course_id}"`).classList.add("warning");
				}
			}
		}
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

		// Fix an issue with redips drag
		// It cannot handle a container being wider than the page or having a horizontal scrollbar
		document.body.style.minWidth = (500 + 120 * cols) + "px";

		for (let i = 0; i < this.plan.semesters.length; i++) {
			let semester = this.plan.semesters[i];
			let tr = document.createElement("tr");

			let th = document.createElement("th");
			th.className = "redips-mark";
			th.innerHTML = semester.toString() + "<br><span class='ch' id='ch"+semester.id+"'>0 credit hours</span>";
			tr.appendChild(th);

			// Delete button (don't show if only one semester)
			if (this.plan.semesters.length > 1 && semester.courses.length == 0) {
				let dele = document.createElement("button");
				dele.className = "btn btn-sm btn-danger delete-semester";
				dele.innerHTML = '<i class="fa fa-trash"></i>';
				dele.addEventListener("click", e => {
					this.plan.remove_semester(semester.id);
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
					if (course.course_semester[semester.season()] != 1) { 
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
	* @param msg {string} The error message about the plan to display to the user
	* @post The message is added to the elements on the page and print layoutt
	**/
	add_error(msg, type="danger") {
		this.makeElement("li", "print-notifications").innerHTML = msg;
		document.getElementById("notifications").innerHTML += `<div class="alert alert-${type} mt-2 mb-0">${msg}</div>`;
		if (type == "danger") {
			this.plan_status = Math.min(this.plan_status, 1); // 1 = Error
		}
		else if (type == "warning") {
			this.plan_status = Math.min(this.plan_status, 2); // 2 = Warning
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
