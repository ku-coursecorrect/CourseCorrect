<!DOCTYPE html>
<?php
	require_once "../common.php";

	// Assemble plan data for use by JavaScript

	if (isset($_GET["plan"])) { // Load a plan from the database
		require_login();
		$plan_id = (int) $_GET["plan"];
		// TODO: Join degree table for name/year - maybe shouldn't have two separate queries
		if ($_SESSION["permissions"] > 0) { // Staff - all staff can view all student plans
			$planQuery = $db->query("SELECT * FROM plan WHERE plan_id = ?", [$plan_id]);
		}
		else { // Student - students can only view their own plans
			$planQuery = $db->query("SELECT * FROM plan WHERE plan_id = ? AND user_id = ?", [$plan_id, $_SESSION["user_id"]]);
		}
		if (count($planQuery) != 1) crash(ErrorCode::PlanNotExist, [$plan_id, $_SESSION["user_id"]]);
		$planRow = $planQuery[0]; // Only one row

		// Get the plan's semesters and transfer credits
		$plan = json_decode($planRow["json"], true);

		$plan["plan_id"] = $plan_id;
		$plan["plan_title"] = $planRow["plan_title"];
		$plan["degree_id"] = $planRow["degree_id"];
	}

	else if (isset($_GET["major"]) && isset($_GET["year"])) { // Guest mode create an unsavable empty plan
		$plan = new_plan_json(intval($_GET["year"]));

		$plan["plan_title"] = "Guest mode";
		$plan["degree_id"] = find_degree_id($_GET["major"], $_GET["year"]);
	}
	else {
		crash(ErrorCode::NoPlanSpecified);
	}

	$degree = $db->query("SELECT major, year FROM degree WHERE degree_id = ?", [$plan["degree_id"]])[0];
	$plan["degree_major"] = $degree["major"];
	$plan["degree_year"] = $degree["year"];

	// Load all courses for this degree
	$courses = $db->query("SELECT * FROM degree_join_course JOIN course USING (course_id) WHERE degree_id = ?", [$plan["degree_id"]]);
	foreach ($courses as &$course) {
		// TODO: incorporate start_semester and end_semester data
		$course["prereq"] = array_column($db->query("SELECT dependent_id FROM requisite WHERE course_id = ? AND co_req = 0", [$course["course_id"]]), "dependent_id");
		$course["coreq"] = array_column($db->query("SELECT dependent_id FROM requisite WHERE course_id = ? AND co_req = 1", [$course["course_id"]]), "dependent_id");
	}
?>
<html>
<head>
	<meta charset="utf-8">
	<title>CourseCorrect</title>
	<link rel="icon" href="../favicon.ico">
  	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Libraries -->
	<link rel="stylesheet" href="../libs/bootstrap.min.css">
	<script src="../libs/jquery.slim.min.js"></script>
	<script src="../libs/popper.min.js"></script>
	<script src="../libs/bootstrap.min.js"></script>
	<script src="../libs/svg.min.js"></script>
	<link rel="stylesheet" href="../libs/fontawesome.min.css">
	<script type="text/javascript" src="../libs/redips-drag.min.js"></script>

	<!-- Application code and style -->
	<link rel="stylesheet" href="style.css">
	<script type="text/javascript" src="Executive.js"></script>
	<script type="text/javascript" src="ArrowRender.js"></script>
	<script type="text/javascript" src="Plan.js"></script>
	<script type="text/javascript" src="Semester.js"></script>
	<script type="text/javascript" src="Course.js"></script>
	<script>
		window.addEventListener('DOMContentLoaded', e => {
			window.executive = new Executive(<?=json_encode($courses)?>, <?=json_encode($plan)?>);
		});
	</script>
</head>
<body>
	<div id="alert_holder"></div>

	<header class="container-fluid py-3">
		<div class="row">
			<div class="col-sm-4">
				<a href="https://ku.edu/"><img src="../images/KUSig_Horz_Web_Blue.png" class="KU_image pt-2 ml-2"></a>
			</div>
			<div class="col-sm-4 text-sm-center KU_color_text">
				<h1><a href="../list">CourseCorrect</a></h1>
			</div>
			<div class="col-sm-4 text-right">
				<!--Student info-->
				<div class="d-inline-block text-left">
					<span class="students_info">Name TODO</span><br>
					<span class="students_info" id="degree_title"></span><br>
					<span class="students_info"><?=isset($_SESSION["user_id"]) ? ("Student ID: " . $_SESSION["kuid"]) : "Not logged in"?></span>
				</div>

				<button class="help-button align-top no-print">Help</button>

				<img src="../images/ku_jayhawk_2.jpg" class="profile_picture align-top no-print">
			</div>
		</div>
	</header>

	<!-- Navigation bar -->
	<nav class="navbar navbar-expand-sm navbar-dark KU_color_background">
		<a class="navbar-brand" href="index.html">Home</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul class="navbar-nav">
				<li class="nav-item">
			  		<a class="nav-link" href="http://classes.ku.edu">Schedule of Classes</a>
				</li>
				<li class="nav-item">
			  		<a class="nav-link" href="http://vsb.ku.edu">Visual Schedule Builder</a>
				</li>
				<li class="nav-item">
			  		<a class="nav-link" href="http://sa.ku.edu">Enroll & Pay</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="https://my.ku.edu/uPortal/">myKU</a>
				</li>
		  	</ul>
		</div>
		<span class="float-right">
			<span id="save-container">
				<!-- TODO: Decide if renaming should make a new plan (disabled for now) -->
				<input type="text" id="plan_title" class="form-control form-control-sm" placeholder="Plan name...">
				<!--Save button-->
				<a id="save-button" type="button" class="btn btn-light btn-sm">Save <i class="fa fa-save"></i></a>
			</span>
			<!--Print button-->
			<a href="javascript:window.print()" type="button" class="btn btn-light btn-sm">Print <i class="fa fa-print"></i></a>
		</span>
	</nav>

	<!--Printing only content (reformatted notifications and other courses)-->
	<div class="container only_print">
		<div class="row mt-3">
			<div class="col-sm-6">
				<h3>Notifications</h3>
				<div class="bg-light border p-3">
					<ul id="print-notifications"></ul>
				</div>
			</div>
			<div class="col-sm-6">
				<h3>Excluded courses</h3>
				<p id="print-course-bank"></p>

				<h3>Transferred courses</h3>
				<p id="print-transfer-bank"></p>
			</div>
		</div>
	</div>

	<!--Content-->
	<div class="container">
		<div class="alert alert-success mt-4" id="plan-exported" style="display:none">
			<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
			Plan data copied to clipboard. You can share this with others or reimport it later.
		</div>

		<div id="redips-drag" class="row">
			<div class="col-lg-4 no-print">
				<div class="my-4">
					<h3>Course Bank</h3>
					<table id="course-bank" class="overflow-auto p-3 mb-3 mb-md-0 mr-md-3 bg-light border" style="min-width: 250px; min-height: 100px;">
						<tr><td></td></tr>
					</table>
				</div>

				<div class="mb-4">
					<h3>Transfer Credits</h3>
					<table id="transfer-bank" class="overflow-auto p-3 mb-3 mb-md-0 mr-md-3 bg-light border" style="min-width: 250px; min-height: 60px;">
						<tr><td></td></tr>
					</table>
				</div>

				<div class="mb-4" id="add_extra_course_box" style="display:none">
					<h3>Add Extra Course</h3>
					<table>
						<tr>
							<td class="text-nowrap pr-2">Course Code:</td>
							<td><input type="text" class="form-control" id="course_code"></td>
						</tr>
						<tr>
							<td class="text-nowrap pr-2">Credit Hours:</td>
							<td>
								<div class="input-group">
									<input type="number" class="form-control" id="credit_hours" name="credit_hours" min="0">
									<div class="input-group-append">
										<button type="submit" class="btn btn-primary" id="course_add_submit">Add</button>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</div>

				<div class="mb-4 mr-4 no-print">
					<h3>Add Semester</h3>
					<div class="input-group">
						<select id="addSemesterSelect" class="form-control">
							<option disabled selected value="-1">Choose a semester...</option>
						</select>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary" id="add-semester-btn">Add</button>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-8 mt-4">
				<div class="d-flex">
					<div id="schedule-container" class="bg-light"> <!--Schedule-->
						<div id="arrows"></div><!--Will contain the SVG with the arrows-->
						<table id="course-grid" class="border"></table><!--Will contain the drag-and-droppable courses-->
						<div id="welcome" class="border p-3">
							<h1>Welcome!</h1>
							<h4>Select your major and first semester at KU semester to begin.</h4>
							<div class="input-group">
								<select id="majorSelect" class="form-control"></select>
								<select id="startSemesterSelect" class="form-control"></select>
								<div class="input-group-append">
									<button type="button" class="btn btn-primary" id="done">Start Planning</button>
								</div>
							</div>
							<div style="display: none !important"><!--TODO TMP-->
							<hr class="my-4">
							<h5>Or load a plan saved in your browser:</h5>
							<div class="input-group mb-2">
								<select id="planSelect" class="form-control">
									<option disabled selected value="-1">Choose a plan...</option>
								</select>
								<div class="input-group-append">
									<button type="button" class="btn btn-primary" id="load-plan">Load Plan</button>
									<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirm-delete-plan">Delete Plan</button>
								</div>
							</div>
							<h5>Or import a previously exported plan:</h5>
							<div class="input-group">
								<textarea class="form-control" id="plan-to-import" placeholder='{"version":1,"timestamp":1604269786608,"major":"Computer Science","course_bank":["EECS 101","EECS 140"],"transfer_bank":[],"semesters":[{"semester_year":2020,"semester_season":2,"semester_courses":["EECS 168"]}]}'></textarea>
								<div class="input-group-append">
									<button type="button" class="btn btn-primary" id="import-plan">Import</button>
								</div>
							</div>
							</div><!--TODO TMP-->
						</div>
					</div>
				</div>

				<div class="modal fade" id="confirm-delete-plan">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">Delete Plan</div>
							<div class="modal-body">Are you sure you wish to delete the selected plan?</div>
							<div class="modal-footer">
								<input type="button" class="btn btn-secondary" data-dismiss="modal" value="Cancel">
								<input type="button" class="btn btn-danger" id="delete-plan" data-dismiss="modal" value="Delete">
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-2 no-print" id="add-semester" style="display:none">
					<div class="col-sm-6 offset-sm-3 border p-3 bg-light">
						<div class="input-group">
							<select id="addSemesterSelect" class="form-control">
								<option disabled selected value="-1">Choose a semester...</option>
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="add-semester-btn">Add semester</button>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-5 no-print">
					<div class="col-sm-6">
						<h3>KU Core links</h3>
						<div class="overflow-auto p-3 mb-3 mb-md-0 mr-md-3 bg-light border scrollable_box">
							<ul>
								<li><a href="https://kucore.ku.edu/courses">List of all approved courses</a></li>
								<li><a href="https://college.ku.edu/winter">Winter break courses</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-6">
						<h3>EECS links</h3>
						<div class="overflow-auto p-3 mb-3 mb-md-0 mr-md-3 bg-light border scrollable_box">
							<ul>
								<li><a href="http://eecs.ku.edu/eecs-courses">List of all EECS courses</a></li>
								<li><a href="http://eecs.ku.edu/current-students/undergraduate">Undergraduate handbook</a></li>
								<li><a href="https://catalog.ku.edu/engineering/electrical-engineering-computer-science/bs-computer-science/">Description of the Bachelor of Science in Computer Science</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<footer class="pt-2 my-2 border-top text-center">
		<a href="https://github.com/ku-coursecorrect/coursecorrect">CourseCorrect</a> Copyright &copy; 2022: Drake Prebyl, James Kraijcek, Rafael Alaras, Reece Mathews, Tiger Ruan
		<br>
		View <a href="README.md">readme</a> for works cited | <a href="documentation/index.html">Documentation</a> | <a href="tests.html">Tests</a>
	</footer>
</body>
</html>
