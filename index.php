<!DOCTYPE html>
<?php
	require_once "common.php";

	// Redirect to plan list or staff homepage if logged in
	if (is_logged_in()) {
		if (is_staff()) header("Location: staff");
		else header("Location: list");
	}
?>
<html lang="en">
<head>
    <title>CourseCorrect</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="libs/bootstrap.min.css">
	<script src="libs/jquery.slim.min.js"></script>
	<script src="libs/popper.min.js"></script>
	<script src="libs/bootstrap.min.js"></script>
</head>
<body>
	<nav class="navbar navbar-light bg-light">
		<a class="navbar-brand" href="https://ku.edu">
			<img src="images/KUSig_Horz_Web_Blue.png" height="30" alt="">
		</a>
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="https://eecs.ku.edu">Electrical Engineering and Computer Science</a>
			</li>
		</ul>
	</nav>
    <div class="container">
		<div class="row">
			<div class="col-lg-8">
				<div class="text-center">
					<img src="images/logo.png" class="m-4 border">
				</div>
				<div class="text-center">
					<a href="login.php"><img src="images/kul_logon_button.png"></a>
				</div>
				<div class="text-center my-3">
					&mdash; OR &mdash;
				</div>
				<div class="text-center">
					<button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#create-plan">Continue as Guest</button>
				</div>
				<div class="text-center my-2">
					<div class="alert alert-warning" role="alert">
						<b>Warning:</b> Guests cannot save plans
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<p class="mt-4" style="white-space: pre-wrap"><?=$db->query("SELECT text FROM help_text WHERE id='WelcomeText'")[0]["text"]?></p>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="create-plan" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="createModalLabel">Continue as Guest</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="GET" action="edit">
					<div class="modal-body">
							<div class="form-group">
								<label for="major">Select major:</label>
								<select id="major" name="major" class="form-control" required>
									<option disabled selected>Select a major...</option>
								</select>
							</div>
							<div class="form-group">
								<label for="year">Select handbook version:</label>
								<select id="year" name="year" class="form-control" required>
								</select>
							</div>
							<div class="text-center my-2">
								<div class="alert alert-warning" role="alert">
									<b>Warning:</b> Guests cannot save plans
								</div>
							</div>
							<script>
								const DEGREES = <?=json_encode($db->query("SELECT major, year FROM degree ORDER BY major, year DESC"))?>;
								
								// Create list of majors, e.g.: {"Computer Science": ["2018", "2019", ...], ...}
								let majors = {};
								for (const degree of DEGREES) {
									const key = degree["major"];
									if (!(key in majors)) majors[key] = [];
									majors[key].push(degree["year"]);
								}
								
								const majorSelect = document.getElementById("major");
								const yearSelect = document.getElementById("year");
								
								for (const major in majors) {
									majorSelect.add(new Option(major, major));
								}
								
								majorSelect.addEventListener("change", e => {
									while (yearSelect.firstChild) yearSelect.removeChild(yearSelect.firstChild); // Clear year dropdown
									for (const year of majors[majorSelect.value]) {
										yearSelect.add(new Option("Fall " + year, year));
									}
								});
							</script>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-success">Continue</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>