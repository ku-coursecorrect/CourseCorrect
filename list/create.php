<?php
	require_once "../common.php";
	require_login();

	$name = $_POST["name"];
	$degree_id = find_degree_id($_POST["major"], $_POST["year"]);
	
	$semesters = [];

	// Populate the plan JSON with empty semesters for the next 4 years
	$startYear = intval($_POST["year"]);
	$season = FALL;
	for ($year = $startYear; $year <= $startYear + 4; $year++) {
		while ($season <= FALL and count($semesters) < 8) {
			$semesters[] = ["semester_year" => $year, "semester_season" => $season, "semester_courses" => []];
			$season += 2;
		}
		$season = SPRING;
	}

	// TODO (maybe): Place to store custom courses
	$json = json_encode(["semesters" => $semesters, "transfer_bank" => []]);
	
	$db->query("INSERT INTO plan (user_id, degree_id, plan_title, json) VALUES (?, ?, ?, ?)", [$_SESSION["user_id"], $degree_id, $name, $json]);
	$plan_id = $db->lastInsertId();

	header("Location: ../edit?plan=" . $plan_id);
?>