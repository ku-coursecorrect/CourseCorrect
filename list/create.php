<?php
	require_once "../common.php";
	$name = $_POST["name"];
	$major = $_POST["major"];
	$year = $_POST["year"];

	$degree = $db->query("SELECT id FROM majors WHERE major = ? AND year = ?", $major, $year);
	if (count($degree) > 1) { // Valid degree
		$degree_id = $degree[0]["id"];
		$db->query("INSERT INTO plans (user_id, degree_id, name) VALUES (?, ?, ?)", [$user_id, $degree_id, $year]);
		// TODO: Get the plan id somehow
		header("Location: ../edit?plan=" . $plan_id);
	}
	else { // Invalid degree
		crash(120, [$_POST["major"], $_POST["year"]]);
	}
?>