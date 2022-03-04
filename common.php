<?php
	// 1 for dev/test, 0 for production (maybe this should be in db_creds.php)
	define("DEBUG", 1);

	abstract class ErrorCode {
		const DBConnectionFailed = 101;
		const DBQueryFailed = 102;
		
		const LoginFailed = 201;
		
		const NotLoggedIn = 301;
		const InsufficientPermission = 302;
		
		const InvalidDegree = 401;

		const PlanNotExist = 501;
		const NoPlanSpecified = 502;

		const PlanSaveFailed = 601;
	}
	
	function crash($errorCode, $data = null) {
		// Dev/test code
		if (DEBUG) {
			http_response_code(500);
			// Find the error name via reflection
			$errorName = "unknown";
			foreach ((new ReflectionClass("ErrorCode"))->getReflectionConstants() as $const) {
				if ($const->getValue() == $errorCode) $errorName = $const->getName();
			}
			echo "<div style='border: 4px dashed black; background: #faa; display: inline-block; font-size: 14px; text-shadow: none; color: black;'>";
			echo "<h1 style='color: red; text-shadow: none;'>Error $errorCode: $errorName</h1>";
			var_dump($data);
			echo "</div>";
		}
		else {
			// Production code
			header("Location: /error.php?code=" . $errorCode);
			// TODO log the data/exception somewhere
		}
		die();
	}
	
	define("DATE_FORMAT", "M jS, Y"); // Mar 15th, 2020

	// Semester seasons
	define("SPRING", 0);
	define("SUMMER", 1);
	define("FALL", 2);

	function semester_id($year, $season) {
		return $year * 3 + $season;
	}

	// Populate a new plan with empty semesters for the next 4 years
	// Fall of start year, spring and fall of next 3 years, then spring of the year after that
	function new_plan_json($startYear) {
		$semesters = [];
		for ($year = $startYear; $year < $startYear + 4; $year++) {
			$semesters[] = ["id" => semester_id($year, FALL), "courses" => []];
			$semesters[] = ["id" => semester_id($year+1, SPRING), "courses" => []];
		}
		$json = json_encode(["semesters" => $semesters, "transfer_bank" => []]);
		return $json;
	}
	
	// Status codes bit flags
	abstract class PlanStatus {
		const Incomplete = 1;
		const Warning = 2;
		const Complete = 4;
		const Submitted = 8;
		const Approved = 16;
	}
	
	function planStatusToHTML($status) {
		$badges = "";
		if ($status & PlanStatus::Incomplete) $badges .= '<span class="badge badge-danger">Incomplete</span>';
		if ($status & PlanStatus::Warning) $badges .= '<span class="badge badge-warning">Warning</span>';
		if ($status & PlanStatus::Submitted) $badges .= '<span class="badge badge-info">Pending</span>';
		if ($status & PlanStatus::Approved) $badges .= '<span class="badge badge-success">Approved</span>';
		return $badges;
	}
	
	require_once __DIR__ . "/db.php";
	// Start the session to keep track of who's logged in
	session_start();

	function is_logged_in() {
		return isset($_SESSION["permissions"]);
	}

	function is_staff() {
		return is_logged_in() && $_SESSION["permissions"] > 0;
	}

	function require_login() {
		if (!is_logged_in()) crash(ErrorCode::NotLoggedIn, $_SESSION);
	}
	
	// Page requires staff permissions to access (TODO: specific permission levels)
	function require_staff() {
		if (!is_staff()) crash(ErrorCode::InsufficientPermission, $_SESSION);
	}

	function find_degree_id($major, $year) {
		$degree = $GLOBALS["db"]->query("SELECT degree_id FROM degree WHERE major = ? AND year = ?", [$major, $year]);
		if (count($degree) == 1) return $degree[0]["degree_id"];
		else crash(ErrorCode::InvalidDegree, [$_POST["major"], $_POST["year"]]);
	}
	
	// TODO: Useful links, maybe different for student and staff
	function display_navbar() {
		?>

	<header class="container-fluid py-3">
		<div class="row">
			<div class="col-sm-4">
				<a href="../"><img class="KU_image" src="../images/eecs_logo.png" height="60"></a>
			</div>
			<div class="col-sm-4 text-sm-center KU_color_text">
				<h1>CourseCorrect</h1>
			</div>
			<div class="col-sm-4 text-right">
				<!--Student info-->
				<div class="d-inline-block text-left">
					<?php if (isset($_SESSION["user_id"])): ?>
						<?=$_SESSION["name"]?>
						<a href="../logout.php" class="btn btn-outline-dark btn-sm no-print">Logout</a>
						<br>
						<span class="only-print">Student ID: <?=$_SESSION["kuid"]?></span>
					<?php else: ?>
						Guest mode (not logged in)
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<!-- Navigation bar -->
	<nav class="navbar navbar-expand-md navbar-dark KU_color_background mb-3">
		<a class="navbar-brand" href="../">Home</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link active" href="../list">Plan list</a>
				</li>
		  	</ul>
		</div>
	</nav>

		<?php
	}

	function display_footer() {
		?>

	<!-- Useful links -->
	<div id="useful_links" class="container no-print">
		<div class="row mt-5">
			<div class="col-lg">
				<h3>Other tools</h3>
				<div class="p-3 mb-3 mr-md-3 bg-light border">
					<ul class="mb-0">
						<li><a href="http://vsb.ku.edu/" target="_blank">Visual schedule builder</a></li>
						<li><a href="http://sa.ku.edu/" target="_blank">Enroll & Pay</a></li>
						<li><a href="http://myku.edu/" target="_blank">myKU</a></li>
					</ul>
				</div>
			</div>
			<div class="col-lg">
				<h3>KU course info</h3>
				<div class="p-3 mb-3 mr-md-3 bg-light border">
					<ul class="mb-0">
						<li><a href="https://classes.ku.edu" target="_blank">Schedule of classes</a></li>
						<li><a href="https://kucore.ku.edu/courses" target="_blank">List of KU Core courses</a></li>
						<li><a href="https://college.ku.edu/winter" target="_blank">Winter break courses</a></li>
					</ul>
				</div>
			</div>
			<div class="col-lg">
				<h3>EECS info</h3>
				<div class="p-3 mb-3 mr-md-3 bg-light border">
					<ul class="mb-0">
						<li><a href="http://eecs.ku.edu/current-students/undergraduate" target="_blank">Undergraduate handbook</a></li>
						<li><a href="https://eecs.drupal.ku.edu/prospective-students/undergraduate/degree-requirements" target="_blank">Degree requirements</a></li>
						<li><a href="http://eecs.ku.edu/eecs-courses" target="_blank">List of all EECS courses</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<!-- Copyright line -->
	<footer class="pt-2 my-2 border-top text-center">
		<a href="https://github.com/ku-coursecorrect/coursecorrect">CourseCorrect</a> Copyright &copy; 2022: Drake Prebyl, James Kraijcek, Rafael Alaras, Reece Mathews, Tiger Ruan
	</footer>
		<?php
	}
	
?>