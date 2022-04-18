<!DOCTYPE html>
<?php
	require_once "../common.php";
	require_staff();
	
	if (isset($_POST["id"])) {
		// TODO: Check this worked, display confirmation/error
		$db->query("UPDATE help_text SET description = ?, text = ? WHERE id = ?",
			[$_POST["description"], $_POST["text"], $_POST["id"]]);
			
		// Reload the page as a GET request (instead of a POST request)
		header("Location: edit-help.php");
		exit();
	}
?>
<html lang="en">
<head>
    <title>CourseCorrect</title>
    <meta charset="utf-8">
	<link rel="icon" href="../favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../libs/bootstrap.min.css">
	<script src="../libs/jquery.slim.min.js"></script>
	<script src="../libs/popper.min.js"></script>
	<script src="../libs/bootstrap.min.js"></script>
	<link rel="stylesheet" href="../libs/fontawesome.min.css">
	<link rel="stylesheet" href="../common.css">
	<script src="../common.js"></script>
</head>
<body>
	<?php display_navbar(true); ?>
    <div class="container">
		<div class="row">
			<div class="col">
				<h1>
					View error logs
				</h1>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Error code</th><th>User</th><th>Date</th><th>Technical data</th>
						</tr>
					</thead>
					<tbody>
						<?php
							require_once "../common.php";
							
							$rows = $db->query("SELECT error_code, error_log.user_id, user.kuid, user.name, data, ts
												FROM error_log
												LEFT JOIN user ON error_log.user_id = user.user_id
												ORDER BY ts DESC
												LIMIT 1000");
							
							foreach ($rows as $row) {
								echo "<tr>";
								echo "<td>" . $row["error_code"] . ": " . errorCodeToName($row["error_code"]) .  "</td>";
								echo "<td>" . $row["kuid"] . ": " . $row["name"] . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($row["ts"])) . "</td>";
								echo "<td style='white-space: pre-wrap'>" . $row["data"] . "</td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php display_footer(); ?>
</body>
</html>