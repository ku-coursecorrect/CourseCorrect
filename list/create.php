<?php
	require_once "../common.php";
	require_login();

	$name = $_POST["name"];
	$degree_id = find_degree_id($_POST["major"], $_POST["year"]);
	$json = json_encode(new_plan_content(intval($_POST["year"])));
	
	$db->query("INSERT INTO plan (user_id, degree_id, plan_title, json) VALUES (?, ?, ?, ?)", [$_SESSION["user_id"], $degree_id, $name, $json]);
	$plan_id = $db->lastInsertId();

	header("Location: ../edit?plan=" . $plan_id);
?>