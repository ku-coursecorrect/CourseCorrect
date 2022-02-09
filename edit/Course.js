/**
* @class
* @description This object is used to represent a single course which can be taken and all information about it
**/
class Course {
	/**
	* @param course_id {string} The primary key of the course in the database
	* @param course_code {string} The code of this course (e.g. EECS 448)
	* @param title {string} A name/short description of the course
	* @param prereq {[string]} A list of course codes that are prerequisites of this course
	* @param coreq {[string]}  A list of course codes that are corequisites of this course
	* @param course_semester {[boolean,boolean,boolean]} Whether the course is offered in SPRING, SUMMER, and FALL (constants are array indicies)
	* @param credit_hour {number} The number of credit hours the course is
	* @param is_custom {boolean} Whether the course was created by the user, default false
	*/
	constructor(course_id, course_code, title, prereq, coreq, course_semester, credit_hour, is_custom = false) {
		this.course_id = course_id;
		this.course_code = course_code;
		this.title = title;
		this.prereq = prereq;
		this.coreq = coreq;
		this.course_semester = course_semester.map(sem => sem == 1);
		this.credit_hour = parseInt(credit_hour);
		this.is_custom = is_custom; // Used when saving plans to strings
	}

	/**
	* @return {string} The HTML for a draggable div representing this course
	**/
	to_html() {
		return '<div class="redips-drag" data-toggle="tooltip" title="' + this.title + '" data-course="' + this.course_id + '" onmouseover="hover(this)" onmouseout="unhover(this)">' + this.course_code + "<br>(" + this.credit_hour + ")</div>";
	}
}

function hover(course) {
	// Get the grid coordinates of the course object from the data attributes of the parent td
	let coords = course.parentElement.dataset.x + "," + course.parentElement.dataset.y;
	let arrows = document.querySelectorAll("#arrows polyline");
	for (arrow of arrows) {
		if (arrow.dataset.from == coords) {
			arrow.style.stroke = "#B20206"; // Darker red (color of bottom/right of course border)
		}
		if (arrow.dataset.to == coords) {
			arrow.style.stroke = "#FF040F"; // Lighter red (color of top/left of course border)
		}
	}
}

function unhover(course) {
	let arrows = document.querySelectorAll("#arrows polyline");
	for (arrow of arrows) {
		arrow.style.stroke = "";
	}
}

// TODO: This should be a method of major which should contain course list
/**
	@param course_id {string} The id of the Course to find,
	@return {Course} The Course object from COURSES matching the coures_code
*/
function course_id_to_object(courses, course_id) {
	return courses.find(course => course.course_id == course_id);
}
