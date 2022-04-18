<?php
	require_once "../common.php";
	require_login();

    $delete_id = $_POST['plan_id_to_delete'];
    $plan_check = $db->query("SELECT plan_title
                                FROM plan
                                WHERE user_id = ? AND plan_id = ?", [$_SESSION["user_id"],$delete_id]);
    if (count($plan_check) == 1)
    {
        $db->query("UPDATE plan
                    SET deleted_ts = CURRENT_TIMESTAMP
                    WHERE user_id = ? AND plan_id = ?", [$_SESSION["user_id"],$delete_id]);
        header("Location: ../list/");
    }
    else
    {
        crash(ErrorCode::InsufficientPermission, "The plan with ID '".$delete_id."' doesn't belong to the user with ID '".$_SESSION["user_id"]."'.");
    }
?>