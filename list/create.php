<?php
	require_once "../common.php";
	require_login();

	$name = $_POST["name"];
	$degree_id = find_degree_id($_POST["major"], $_POST["year"]);
	
	// Populate the plan with empty semesters for the next 4 years
	$semesters = [];
	$startYear = intval($_POST["year"]);
	for ($year = $startYear; $year < $startYear + 4; $year++) {
		$semesters[] = ["year" => $year, "season" => FALL, "courses" => []];
		$semesters[] = ["year" => $year+1, "season" => SPRING, "courses" => []];
	}

	// TODO (maybe): Place to store custom courses
	$json = json_encode(["semesters" => $semesters, "transfer_bank" => []]);
	
	$db->query("INSERT INTO plan (user_id, degree_id, plan_title, json) VALUES (?, ?, ?, ?)", [$_SESSION["user_id"], $degree_id, $name, $json]);
	$plan_id = $db->lastInsertId();

	header("Location: ../edit?plan=" . $plan_id);
?>