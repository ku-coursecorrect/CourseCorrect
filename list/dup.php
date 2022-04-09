<?php
	require_once "../common.php";
	require_login();

    $new_name = $_POST['name_change_field'];
    $original_id = $_POST['og_plan_id'];

    $og_plan_check = $db->query("SELECT plan_title
                                FROM plan
                                WHERE user_id = ? AND plan_id = ?", [$_SESSION["user_id"],$original_id]);
    if (count($og_plan_check) == 1)
    {
        $db->query("INSERT INTO 
                    plan (plan_title, user_id, degree_id, plan_status, json)
                        SELECT ?, user_id, degree_id, plan_status, json
                        FROM plan
                        WHERE user_id = ? AND plan_id = ?;", [$new_name, $_SESSION["user_id"], $original_id]);
        $new_plan_id = $db->query("SELECT plan_id FROM plan WHERE user_id = ? AND plan_title = ?", [$_SESSION["user_id"],$new_name]);
        echo("completed execution");
        header("Location: ../edit?plan=". $new_plan_id[0]["plan_id"]."");
    }
    else
    {
        crash(ErrorCode::InsufficientPermission, "The plan with ID '".$original_id."' doesn't belong to the user with ID '".$_SESSION["user_id"]."'.");
    }
?>