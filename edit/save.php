<?php
    require_once "../common.php";
	require_login();

    // user_id included to ensure the plan belongs to the student
    $result = $db->query("UPDATE plan SET plan_title = ?, json = ? WHERE plan_id = ? AND user_id = ?",
        [$_POST["plan_title"], $_POST["json"], $_POST["plan_id"], $_SESSION["user_id"]]);

    if (!$result) crash(ErrorCode::PlanSaveFailed, $_POST);
?>