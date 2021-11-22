<!DOCTYPE html>
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
	<script src="../common.js"></script>
</head>
<body>
	<nav class="navbar navbar-light bg-light mb-4">
		<a class="navbar-brand" href="https://ku.edu">
			<img src="../images/KUSig_Horz_Web_Blue.png" height="30" alt="">
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
				<h1>
					My saved plans
					<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#create-plan"><i class="fas fa-plus"></i> Create new plan</button>
				</h1>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Name</th><th>Major</th><th>Created</th><th>Modified</th><th></th>
						</tr>
					</thead>
					<tbody>
						<?php
							require_once "../common.php";
							
							// TODO: These will come from database queries
							$plans = [
								["id" => 1, "name" => "My official plan", "major" => "Computer Science 2018", "created" => "2018-10-28", "modified" => "2021-10-12", "status" => 4+16],
								["id" => 2, "name" => "Astronomy test", "major" => "IC Astronomy 2018", "created" => "2020-03-01", "modified" => "2020-05-23", "status" => 2],
								["id" => 3, "name" => "EECS 101 plan", "major" => "Computer Science 2018", "created" => "2018-10-18", "modified" => "2018-10-31", "status" => 1],
							];
							
							foreach ($plans as $plan) {
								echo "<tr>";
								echo "<td>" . $plan["name"] . planStatusToHTML($plan["status"]) . "</td>";
								echo "<td>" . $plan["major"] . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($plan["created"])) . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($plan["modified"])) . "</td>";
								echo '<td class="text-nowrap">';
								echo '<a href="../edit?plan=' . $plan["id"] . '" class="text-dark"><i class="fas fa-edit"></i></a>';
								echo '<i class="fas fa-trash ml-3"></i>'; // TODO: Display a delete confirmation modal
								echo "</td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
			<div class="col-lg-4">
				<p>
					This is a place that some help text could be included about how to use this page.
				</p>
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ultrices lorem mi, vel dapibus diam posuere eu. Aliquam facilisis iaculis ipsum venenatis venenatis. Phasellus vulputate, ipsum quis mattis viverra, lectus dui sodales libero, id consequat massa justo ut magna. Donec sed ullamcorper metus. Donec lorem mauris, gravida eu pharetra nec, rutrum a arcu. Cras cursus eget nisl id luctus. Pellentesque sit amet sagittis felis.
				</p>
			</div>
		</div>
	</div>
	
	<!-- Modal -->
	<div class="modal fade" id="create-plan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Create new plan</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="POST" action="create.php">
					<div class="modal-body">
							<div class="form-group">
								<label for="name">Enter plan name:</label>
								<input type="text" id="name" name="name" class="form-control" placeholder="My awesome graduation plan">
							</div>
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
							<script>
								const DEGREES = <?=json_encode($db->query("SELECT name AS major, year FROM degrees ORDER BY major, year DESC"))?>;
								
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
						<button type="submit" class="btn btn-success">Create</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>