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
	}
	
	function crash($errorCode, $data = null) {
		// Dev/test code
		if (DEBUG) {
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
			header("Location: /error.html?code=" . $errorCode);
			// TODO log the data/exception somewhere
		}
		die();
	}
	
	define("DATE_FORMAT", "M jS, Y"); // Mar 15th, 2020

	// Semester seasons
	define("SPRING", 0);
	define("SUMMER", 1);
	define("FALL", 2);
	
	// Status codes bit flags
	abstract class PlanStatus {
		const Complete = 1;
		const Incomplete = 2;
		const Warning = 4;
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

	function require_login() {
		if (!isset($_SESSION["permissions"])) crash(ErrorCode::NotLoggedIn, $_SESSION);
	}
	
	// Page requires staff permissions to access (TODO: specific permission levels)
	function require_staff() {
		require_login();
		if ($_SESSION["permissions"] < 1) crash(ErrorCode::InsufficientPermission, $_SESSION);
	}

	function find_degree_id($major, $year) {
		$degree = $GLOBALS["db"]->query("SELECT degree_id FROM degree WHERE major = ? AND year = ?", [$major, $year]);
		if (count($degree) == 1) return $degree[0]["degree_id"];
		else crash(ErrorCode::InvalidDegree, [$_POST["major"], $_POST["year"]]);
	}
	
	// TODO: Useful links, maybe different for student and staff
	function display_navbar() {
		?>
	<nav class="navbar navbar-light bg-light">
		<a class="navbar-brand" href="https://ku.edu">
			<img src="../images/KUSig_Horz_Web_Blue.png" height="30" alt="">
		</a>
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="https://eecs.ku.edu">Electrical Engineering and Computer Science</a>
			</li>
		</ul>
		<span class="navbar-text">
			<?= $_SESSION["kuid"] ?? "Not logged in" ?>
		</span>
	</nav>
		<?php
	}
	
?>