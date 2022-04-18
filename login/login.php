<?php
	require_once "../common.php";
	if (isset($_POST["username"])) {
		$user = $db->query("SELECT user_id, kuid, permissions, name FROM user WHERE username = ?", [$_POST["username"]]);
		if (count($user) < 1) crash(ErrorCode::LoginFailed);
		$user = $user[0];
		
		$_SESSION["user_id"] = (int) $user["user_id"];
		$_SESSION["kuid"] = $user["kuid"];
		$_SESSION["permissions"] = (int) $user["permissions"];
		$_SESSION["name"] = $user["name"];
		
		if ($user["permissions"] > 0) {
			header("Location: ../staff");
		}
		else {
			header("Location: ../list");
		}
		exit();
	}