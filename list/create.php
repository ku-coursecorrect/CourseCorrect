<?php
	require_once "../common.php";
	require_login();

	$name = $_POST["name"];
	$major = $_POST["major"];
	$year = $_POST["year"];

	$degree = $db->query("SELECT id FROM degrees WHERE major = ? AND year = ?", [$major, $year]);
	if (count($degree) == 1) { // Valid degree
		$degree_id = $degree[0]["id"];
		// TODO (maybe): Handle created/modified date columns
		$db->query("INSERT INTO plans (user_id, degree_id, plan_title) VALUES (?, ?, ?)", [$_SESSION["id"], $degree_id, $name]);
		$plan_id = $db->lastInsertId();
		header("Location: ../edit?plan=" . $plan_id);
	}
	else { // Invalid degree
		crash(ErrorCode::InvalidDegree, [$_POST["major"], $_POST["year"]]);
	}
?>