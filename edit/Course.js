/**
* @class
* @description This object is used to represent a single course which can be taken and all information about it
**/
class Course {
	/**
	* @param course_code {string} The code of this course (e.g. EECS 448)
	* @param title {string} A name/short description of the course
	* @param prereq {[string]} A list of course codes that are prerequisites of this course
	* @param coreq {[string]}  A list of course codes that are corequisites of this course
	* @param course_semester {[bool,bool,bool]} Whether the course is offered in SPRING, SUMMER, and FALL (constants are array indicies)
	* @param credit_hour {number} The number of credit hours the course is
	* @param is_custom {bool} Whether the course was created by the user, default false
	*/
	constructor(course_code, title, prereq, coreq, course_semester, credit_hour, is_custom = false) {
		this.course_code = course_code;
		this.title = title;
		this.prereq = prereq;
		this.coreq = coreq;
		this.course_semester = course_semester;
		this.credit_hour = credit_hour;
		this.is_custom = is_custom; // Used when saving plans to strings
	}

	/**
	* @return {string} The HTML for a draggable div representing this course
	**/
	to_html() {
		return '<div class="redips-drag" data-toggle="tooltip" title="' + this.title + '" data-course="' + this.course_code + '" onmouseover="hover(this)" onmouseout="unhover(this)">' + this.course_code + "<br>(" + this.credit_hour + ")</div>";
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

COURSES = [
	new Course("EECS 101", "New Student Seminar", [], ["MATH 104"], [0,0,1], 1),
	new Course("EECS 140", "Introd to Digital Logic Design", [], ["MATH 104"], [1,0,1], 4),
	new Course("EECS 168", "Programming I",  [], ["MATH 104"], [1,1,1], 4),
	new Course("EECS 202", "Circuits I", ["MATH 220", "MATH 290"], [], [1,0,1], 4),
	new Course("EECS 210", "Discrete Structures", ["EECS 168", "MATH 126"], [], [1,0,1], 4),
	new Course("EECS 212", "Circuits II", ["EECS 221"], [], [1,0,1], 4),
	new Course("EECS 221", "Elecomagnetics I", ["MATH 127", "MATH 220", "EECS 211", "PHSX 210"], [], [1,0,1], 3),
	new Course("EECS 268", "Programming II", ["EECS 168"], [], [1,1,1], 4),
	new Course("EECS 312", "Electronic Circuits I", [], ["EECS 212"], [], 3),
	new Course("EECS 360", "Signal and System Analysis", ["EECS 212"], [], [1,0,1], 3),
	new Course("EECS 368", "Programming Language Paradigms", ["EECS 268"], [], [1,0,1], 3),
	new Course("EECS 388", "Embedded Systems", ["EECS 140", "EECS 168"], [], [1,0,1], 4),
	new Course("EECS 412", "Electronic Circuits II", ["EECS 312"], [], [1,0,1], 4),
	new Course("EECS 420", "Electromagnetics II", ["EECS 220"], [], [1,0,0], 4),
	new Course("EECS 443", "Digital Systems Design", ["EECS 388"], [], [1,0,1], 4),
	new Course("EECS 444", "Control Systems", ["EECS 212", "EECS 360"], [], [1,0,1], 3),
	new Course("EECS 448", "Software Engineering I", ["EECS 268"], [], [1,0,1], 4),
	new Course("EECS 470", "ELectronic Devices", ["PHSX 313"], [], [1,0,0], 3),
	new Course("EECS 501", "Senior Design Lab I", ["EECS 221", "EECS 360", "EECS 412"], [], [1,0,0], 3),
	new Course("EECS 502", "Senior Design Lab II", ["EECS 501"], [], [0,0,1], 3),
	new Course("EECS 510", "Introdn to Theory of Computing", ["EECS 210"], [], [1,0,1], 3),
	new Course("EECS 541", "Computer System Lab I", ["EECS 443", "EECS 448"], [], [1,0,0], 3),
	new Course("EECS 542", "Computer System Lab II", ["EECS 541"], [], [0,0,1], 3),
	new Course("EECS 560", "Data Structures", ["EECS 210", "EECS 448"], [], [1,0,1], 4),
	new Course("EECS 562", "Intro to Comm System", ["EECS 212", "EECS 360"], [], [1,0,1], 4),
	new Course("EECS 563", "Intro to Comm Network", ["EECS 168", "MATH 526", "EECS 461"], [], [1,0,0], 3),
	new Course("EECS 581", "Computer Science Design I", ["EECS 448"], ["EECS 510", "EECS 560"], [0,0,1], 3),
	new Course("EECS 582", "Computer Science Design II", ["EECS 581"], [], [1,0,0], 3),
	new Course("EECS 645", "Computer Architecture", ["EECS 388"], [], [1,0,1], 3),
	new Course("EECS 660", "Fndmntls of Computer Algorthms", ["EECS 560", "MATH 526"], [], [1,0,1], 3),
	new Course("EECS 662", "Programming Languages", ["EECS 368", "EECS 560"], [], [1,0,1], 3),
	new Course("EECS 665", "Compiler Construction", ["EECS 368", "EECS 448", "EECS 510"], [], [1,0,1], 4),
	new Course("EECS 678", "Introdctn to Operating Systems", ["EECS 388", "EECS 448"], [], [1,0,1], 4),
	new Course("Sen Elec 1", "Most 400+ EECS courses", [], [], [1,0,1], 3),
	new Course("Sen Elec 2", "Most 400+ EECS courses", [], [], [1,0,1], 3),
	new Course("Sen Elec 3", "Most 400+ EECS courses", [], [], [1,0,1], 3),
	new Course("Sen Elec 4", "Most 400+ EECS courses", [], [], [1,0,1], 3),
	new Course("MATH 125", "Calculus I",  [], [], [1,1,1], 4),
	new Course("MATH 126", "Calculus II", ["MATH 125"], [], [1,1,1], 4),
	new Course("MATH 127", "Calculus III", ["MATH 126"], [], [1,1,1], 4),
	new Course("MATH 220", "Differential Equations", ["MATH 126"], ["MATH 290"], [1,0,1], 3),
	new Course("MATH 290", "Elementary Linear Algebra", ["MATH 126"], [], [1,1,1], 2),
	new Course("MATH 526", "Appld Mathematicl Statistics I", ["MATH 127"], [], [1,1,1], 3),
	new Course("PHSX 210", "General Physics I for Engineers", ["MATH 125"], ["MATH 126"], [1,0,1], 3),
	new Course("PHSX 216", "General Physics I Laboratory", [], ["PHSX 210"], [1,1,1], 1),
	new Course("PHSX 212", "General Physics II", ["PHSX 210", "MATH 126"],[], [1,1,1], 3),
	new Course("PHSX 236", "General Physics II Laboratory", [], ["PHSX 212"], [1,1,1], 1),
	new Course("PHSX 313", "General Physics III", ["PHSX 212", "PHSX 236", "EECS 221", "EECS 220", "EECS 221"], ["MATH 220"], [1,1,1], 3),
	new Course("PHSX 316", "General Physics III Labratory", [], ["PHSX 313"], [1,1,1], 1),
	new Course("PHSX 503", "Undergraduate Research", [], [], [1,0,1], 4),
	new Course("PHSX 511", "Introductory Quantum Mechanics", ["PHSX 313", "MATH 290"], [], [1,0,1], 3),
	new Course("PHSX 521", "Mechanics I", ["PHSX 210", "PHSX 216", "MATH 127", "MATH 290", "MATH 220"], [], [1,0,0], 3),
	new Course("PHSX 531", "Electricity and Magnetism", ["PHSX 212", "PHSX 236", "PHSX 521", "MATH 127", "MATH 290", "MATH 220"], [], [1,0,0], 3),
	new Course("PHSX Elec 1", "Most 600+ PHSX courses", [], [], [1,1,1], 3),
	new Course("PHSX Elec 2", "Most 600+ PHSX courses", [], [], [1,1,1], 3),
	new Course("ECON 142/144", "Micro/Macro Economics", [], [], [1,1,1], 3),
	new Course("CHEM 130/150", "General Chemistry I/Engineering Chemistry", [], [], [1,1,1], 5),
	new Course("CHEM 130", "General Chemistry I", [], [], [1,1,1], 5),
	new Course("CHEM 135", "General Chemistry II", ["CHEM 130"], [], [1,1,1], 5),
	new Course("CHEM 330", "Organic Chemistry I", ["CHEM 135"], [], [1,0,1], 3),
	new Course("CHEM 530", "Physical Chemistry I", ["PHSX 212", "MATH 127", "MATH 220"], ["MATH 290"], [1,0,1], 4),
	new Course("CHEM 535", "Physical Chemistry II", ["CHEM 530"], [], [1,0,1], 3),
	new Course("CHEM 537", "Physical Chemistry Lab", ["CHEM 530"], [], [1,0,1], 3),
	new Course("CHEM 698", "Undergraduate Capstone", [], [], [1,0,0], 2),
	new Course("ASTR 391", "Physical Astronomy Honors", ["MATH 125"], [], [1,0,1], 3),
	new Course("ASTR 503", "Undergraduate Research", [], [], [1,0,1], 4),
	new Course("ASTR 591", "Stellar Astronomy", ["PHSX 212", "PHSX 236"], [], [1,0,1], 3),
	new Course("ASTR 592", "Galactic and Extragalactic Astronomy", ["ASTR 591"], [], [1,0,1], 3),
	new Course("ASTR 596", "Observational Astrophysics", [], ["ASTR 591"], [1,0,1], 3),
	new Course("ASTR Elec 1", "Most 500+ ASTR courses", [], [], [1,0,1], 3),
	new Course("ASTR Elec 2", "Most 500+ ASTR courses", [], [], [1,0,1], 3),
	new Course("GEOG Basics 1", "Most 300+ GEOG courses", [], [], [1,0,1], 3),
	new Course("GEOG Basics 2", "Most 300+ GEOG courses", [], [], [1,0,1], 3),
	new Course("GEOG 311", "Introductory Cartography and Geovisualization", [], [], [1,0,1], 4),
	new Course("GEOG 358", "Introduction to Geographic Information Systems", [], [], [1,0,1], 4),
	new Course("GEOG 526", "Remote Sensing of Environment I", ["GEOG 358"], [], [1,0,1], 4),
	new Course("GEOG 558", "Intermediate Geographical Information Systems", ["GEOG 358"], [], [1,0,1], 4),
	new Course("GEOG Elec 1", "Select 500+ GEOG courses", [], [], [1,0,1], 3),
	new Course("GEOG Elec 2", "Select 500+ GEOG courses", [], [], [1,0,1], 3),
	new Course("BIOL 150", "Principles of Moleculare and Cellular Biology", [], ["CHEM 130"], [1,0,1], 4),
	new Course("BIOL 152", "Principles of Organismal Biology", ["BIOL 150"], [], [1,0,1], 4),
	new Course("BIOL 350", "Principles of Genetics", ["CHEM 135", "BIOL 150", "BIOL 152"], [], [1,0,1], 4),
	new Course("BIOL 412", "Evolutionary Biology", ["BIOL 152", "BIOL 350"], [], [1,0,1], 4),
	new Course("BIOL Elec 1", "Select 400+ BIOL courses", [], [], [1,0,1], 3),
	new Course("BIOL Elec 2", "Select 400+ BIOL courses", [], [], [1,0,1], 3),
	new Course("JOUR 101", "Media and Society", [], [], [1,0,1], 3),
	new Course("JOUR 300", "Visual Storytelling", ["JOUR 101"], [], [1,0,1], 3),
	new Course("JOUR 302", "Information Exploration", [], [], [1,0,1], 3),
	new Course("JOUR 304", "Media Writing for Audiences", ["JOUR 101", "JOUR 104"], ["JOUR 302"], [1,0,1], 3),
	new Course("JOUR 320", "Strategic Communications", ["JOUR 101"], [], [1,0,1], 3),
	new Course("JOUR 415", "Multimedia Reporting", ["JOUR 302", "JOUR 304"], [], [1,0,1], 3),
	new Course("JOUR 419", "Multimedia Editing", ["JOUR 302", "JOUR 304"], [], [1,0,1], 3),
	new Course("JOUR 618", "First Amendment and Society", [], [], [1,0,1], 3),
	new Course("JOUR Elec 1", "Select 400+ JOUR courses", [], [], [1,0,1], 3),
	new Course("JOUR Elec 2", "Select 400+ JOUR courses", [], [], [1,0,1], 3),
	new Course("JOUR Elec 3", "Select 400+ JOUR courses", [], [], [1,0,1], 3),
	new Course("PHIL 375/320/160", "Intro Level PHIL Course", [], [], [1,1,1], 3),
	new Course("Prof Elec 1", "Professional Elective", [], [], [1,1,1], 3),
	new Course("Prof Elec 2", "Professional Elective", [], [], [1,1,1], 3),
	new Course("GE 3N", "Natural Science (typically CHEM 150 Chemistry for Engineers which is 5 credit hours)", [], [], [1,1,1], 3),
	new Course("GE 2.1(1)", "Written Communication I (typically ENGL 101 Composition or exempt from ACT score)", [], [], [1,1,1], 3),
	new Course("GE 2.1(2)", "Written Communication II (typically ENGL 102 Critical Reading and Writing)", [], [], [1,1,1], 3),
	new Course("GE 2.2", "Oral Communication (typically COMS 130 Speaker-Audience Communication)", [], [], [1,1,1], 3),
	new Course("GE 3H", "Arts and Humanities", [], [], [1,1,1], 3),
	new Course("GE 3S", "Social Science", [], [], [1,1,1], 3),
	new Course("Add A/H", "Additional GE 3H course", [], [], [1,1,1], 3),
	new Course("Add A/H/SS", "Additional GE 3H course", [], [], [1,1,1], 3),
	new Course("Add SS", "Additional GE 3S course", [], [], [1,1,1], 3),
	new Course("AE 4.1", "United States cultural diversity", [], [], [1,1,1], 3),
	new Course("AE 4.2", "Global cultural diversity", [], [], [1,1,1], 3),
	new Course("AE 5.1", "Social Responsibility and Ethics", [], [], [1,1,1], 3)
];

ULE_EXCECPTIONS = ["EECS 312","EECS 361","EECS 368","EECS 388"];
