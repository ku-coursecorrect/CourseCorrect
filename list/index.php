<!DOCTYPE html>
<?php
	require_once "../common.php";
	require_login();
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
	<?php display_navbar(); ?>
    <div class="container">
		<div class="row">
			<div class="col-lg-9">
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
							
							$plans = $db->query("SELECT plan_id, 
													    plan_title, 
														degree.major, 
														degree.year, 
														plan.created_ts, 
														plan.modified_ts, 
														plan_status 
												FROM plan 
												JOIN degree ON plan.degree_id = degree.degree_id
												WHERE user_id = ?
												ORDER BY modified_ts DESC", [$_SESSION["user_id"]]);
							
							foreach ($plans as $plan) {
								echo "<tr>";
								echo "<td>" . $plan["plan_title"] . planStatusToHTML($plan["plan_status"]) . "</td>";
								echo "<td>" . $plan["major"]  . " " . $plan["year"] . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($plan["created_ts"])) . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($plan["modified_ts"])) . "</td>";
								echo '<td class="text-nowrap">';
								echo '<a href="../edit?plan=' . $plan["plan_id"] . '" class="text-dark" title="Edit"><i class="fas fa-edit"></i></a>';
								echo '<i class="fas fa-copy ml-3" title="Duplicate"></i>'; // TODO: Create copy of plan (either one click or a modal for new name)
								echo '<i class="fas fa-trash ml-3" title="Delete"></i>'; // TODO: Display a delete confirmation modal
								echo "</td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
			<script>
				$('body').tooltip({selector: '[title]'});
			</script>
			<div class="col-lg-3">
				<p>
					This is a place that some help text could be included about how to use this page.
				</p>
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ultrices lorem mi, vel dapibus diam posuere eu. Aliquam facilisis iaculis ipsum venenatis venenatis. Phasellus vulputate, ipsum quis mattis viverra, lectus dui sodales libero, id consequat massa justo ut magna. Donec sed ullamcorper metus. Donec lorem mauris, gravida eu pharetra nec, rutrum a arcu. Cras cursus eget nisl id luctus. Pellentesque sit amet sagittis felis.
				</p>
			</div>
		</div>
	</div>

	<?php display_footer(); ?>
	
	<!-- Modal -->
	<div class="modal fade" id="create-plan" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="createModalLabel">Create new plan</h5>
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
								
								// TODO: This also needs to run if browser remembers dropdown (maybe just make form autocomplete off)
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