<?php
	require_once "common.php";
	if (isset($_POST["kuid"])) {
		$user = $db->query("SELECT id, kuid, permissions FROM users WHERE kuid = ?", [$_POST["kuid"]]);
		if (count($user) < 1) crash(400);
		$user = $user[0];
		
		$_SESSION["id"] = (int) $user["id"];
		$_SESSION["kuid"] = $user["kuid"];
		$_SESSION["permissions"] = (int) $user["permissions"];
		
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
	<select name="kuid">
		<?php
			$users = $db->query("SELECT id, kuid, permissions FROM users");
			foreach ($users as $user) {
				echo "<option value='" . $user["kuid"] . "'>" . json_encode($user) . "</option>";
			}
		?>
	</select>
	<input type="submit">
</form>