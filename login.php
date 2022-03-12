<?php
	require_once "common.php";
	if (isset($_POST["kuid"])) {
		$user = $db->query("SELECT user_id, kuid, permissions, name FROM user WHERE kuid = ?", [$_POST["kuid"]]);
		if (count($user) < 1) crash(ErrorCode::LoginFailed);
		$user = $user[0];
		
		$_SESSION["user_id"] = (int) $user["user_id"];
		$_SESSION["kuid"] = $user["kuid"];
		$_SESSION["permissions"] = (int) $user["permissions"];
		$_SESSION["name"] = $user["name"];
		
		if ($user["permissions"] > 0) {
			header("Location: staff");
		}
		else {
			header("Location: list");
		}
		exit();
	}
?>
<h1>Dev/test login page</h1>
<form method="POST">
	Select a user:
	<?php
		$users = $db->query("SELECT name, user_id, kuid, permissions FROM user");
		foreach ($users as $user) {
			echo "<br><button type='submit' name='kuid' value='" . $user["kuid"] . "'>" . json_encode($user) . "</button>";
		}
	?>
</form>