<?php
	// 1 for dev/test, 0 for production (maybe this should be in db_creds.php)
	define("DEBUG", 0);

	// Allow vars defined in env_vars to be found in globals
	if (file_exists("env_vars.php")) {
		require_once "env_vars.php";
	} else {
		// Defaults in case env_vars.php is missing
		$ENV_VARS["project_root"] = "/";
	}
	$GLOBALS = array_merge($GLOBALS, $ENV_VARS);

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

	function errorCodeToName($errorCode) {
		// Find the error name via reflection
		$errorName = "unknown";
		foreach ((new ReflectionClass("ErrorCode"))->getReflectionConstants() as $const) {
			if ($const->getValue() == $errorCode) $errorName = $const->getName();
		}
		return $errorName;
	}
	
	function crash($errorCode, $data = null) {
		if (DEBUG) {
			// Dev/test code
			http_response_code(500);
			$errorName = errorCodeToName($errorCode);
			echo "<div style='border: 4px dashed black; background: #faa; display: inline-block; font-size: 14px; text-shadow: none; color: black;'>";
			echo "<h1 style='color: red; text-shadow: none;'>Error $errorCode: $errorName</h1>";
			var_dump($data);
			echo "</div>";
		}
		else {
			// Production code
			$userId = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;
			$data = var_export($data, true);

			try { // Log error to database
				// db.php is not used in case the error is with the database connection
				include_once 'db_creds.php';
				global $DB_INFO;
				$conn = new PDO($DB_INFO["DB_TYPE"] . ":host=" . $DB_INFO["HOST"] . ";dbname=" . $DB_INFO["DB_NAME"], $DB_INFO["USER"], $DB_INFO["PASSWORD"]);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$stmt = $conn->prepare("INSERT INTO error_log (error_code, user_id, data) VALUES (?, ?, ?)");
				$stmt->execute([$errorCode, $userId, $data]);
			}
			catch (PDOException $e) { // Database failed, log error to file
				ob_start();
				echo date("Y-m-d H:i:s") . " logging error to database failed:\n";
				echo "User ID: $userId\n";
				echo "Error code: $errorCode\n";
				echo "Error data: $data\n";
				echo "PDO Exception: ";
				var_dump($e);
				echo "\n\n";
				$text = ob_get_clean();
				file_put_contents(__DIR__ . "/emergency_error_log.txt", $text, FILE_APPEND | LOCK_EX);
			}
			
			// Redirect to error page
			header("Location: /error.php?code=" . $errorCode);
		}
		die();
	}
	
	define("DATE_FORMAT", "M jS, Y"); // Mar 15th, 2020

	// Semester seasons
	define("SPRING", 0);
	define("SUMMER", 1);
	define("FALL", 2);

	const SEASON_NAME = [
		0 => "Spring",
		1 => "Summer",
		2 => "Fall"
	];
	
	function semester_id($year, $season) {
		return $year * 3 + $season;
	}

	// Convert a semester format to a year
	function semester_year($semester) {
		return floor($semester / 3);
	}

	// Convert a semester format to a season string
	function semester_season($semester) {
		return SEASON_NAME[$semester % 3];
	}

	// Populate a new plan with empty semesters for the next 4 years
	// Fall of start year, spring and fall of next 3 years, then spring of the year after that
	function new_plan_content($startYear) {
		$semesters = [];
		for ($year = $startYear; $year < $startYear + 4; $year++) {
			$semesters[] = ["id" => semester_id($year, FALL), "courses" => []];
			$semesters[] = ["id" => semester_id($year+1, SPRING), "courses" => []];
		}
		return ["semesters" => $semesters, "transfer_bank" => [], "notes" => ""];
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
	function display_navbar($staff = false) {
		?>

	<header class="container-fluid py-3">
		<div class="row">
			<div class="col-sm-4">
				<a href="<?=$GLOBALS['project_root']?>"><img class="KU_image" src="<?=$GLOBALS['project_root']?>images/eecs_logo.png" height="60"></a>
			</div>
			<div class="col-sm-4 text-sm-center <?=$staff?"text-danger":"KU_color_text"?>">
				<h1>CourseCorrect</h1>
			</div>
			<div class="col-sm-4 text-right">
				<!--Student info-->
				<div class="d-inline-block text-left">
					<?php if (isset($_SESSION["user_id"])): ?>
						<?=$_SESSION["name"]?>
						<a href="<?=$GLOBALS['project_root']?>logout.php" class="btn btn-outline-dark btn-sm no-print">Logout</a>
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
	<nav class="navbar navbar-expand-md navbar-dark <?=$staff?"bg-danger":"KU_color_background"?> mb-3">
		<a class="navbar-brand" href="<?=$GLOBALS['project_root']?>">Home</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul class="navbar-nav">
				<?php
					// TODO: Nav items based on staff permission level
					if ($staff) {
						$items = [
							$GLOBALS['project_root'] . "staff/edit-degrees.php" => "Edit degrees", 
							$GLOBALS['project_root'] . "staff/edit-courses/edit-courses.php" => "Edit courses", 
							$GLOBALS['project_root'] . "staff/edit-help.php" => "Edit help text",
							$GLOBALS['project_root'] . "staff/view-errors.php" => "View errors",
						];
					}
					else {
						$items = [$GLOBALS['project_root'] . "list" => "Plan list"];
					}
					foreach ($items as $url => $text) {
						?>
							<li class="nav-item">
								<a class="nav-link active" href="<?=$url?>"><?=$text?></a>
							</li>
						<?php
					}
				?>
		  	</ul>
		</div>
	</nav>

		<?php
	}

	function display_footer() {
		?>

	<!-- Copyright line -->
	<footer class="pt-2 mt-5 pb-2 border-top">
		<div class="container">
			<div class="row my-2">
				<?php
					$text = $GLOBALS["db"]->query("SELECT text FROM help_text WHERE id = 'FooterLinks'")[0]["text"];
					$text = str_replace("\r", "", $text); // Remove any carriage returns
					$sections = explode("\n\n", $text);
					foreach ($sections as $section) {
						$links = explode("\n", $section);
						$heading = array_shift($links);
						?>
							<div class="col-lg">
								<div class="card">
									<div class="card-body">
										<h5 class="card-title"><?=$heading?></h5>
										<ul class="mb-0">
											<?php
												foreach($links as $link) {
													$link = explode(" ", $link, 2);
													$url = $link[0];
													$label = $link[1];
													echo "<li><a href='$url' target='_blank'>$label</a></li>";
												}
											?>
										</ul>
									</div>
								</div>
							</div>
						<?php
					}
				?>
			</div>
			<div class="row">
				<div class="col text-center">
					<a href="https://github.com/ku-coursecorrect/coursecorrect">CourseCorrect</a> Copyright &copy; 2022: Drake Prebyl, James Krajicek, Rafael Alaras, Reece Mathews, Tiger Ruan
				</div>
			</div>
		</div>
	</footer>
		<?php
	}
	
?>