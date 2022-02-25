<?php
    require_once "../common.php";
	require_login();

    // Ensure the plan belongs to the user
    // This is a separate query because an update which matches a row, but does not change any values, will return 0 rows affected
    $plan = $db->query("SELECT plan_title FROM plan WHERE plan_id = ? AND user_id = ?",
        [$_POST["plan_id"], $_SESSION["user_id"]]);
    if (count($plan) != 1) crash(ErrorCode::PlanSaveFailed, $_POST);

    // user_id included to ensure the plan belongs to the student
    $db->query("UPDATE plan SET plan_title = ?, plan_status = ?, json = ? WHERE plan_id = ? AND user_id = ?",
        [$_POST["plan_title"], $_POST["plan_status"], $_POST["json"], $_POST["plan_id"], $_SESSION["user_id"]]);
?>