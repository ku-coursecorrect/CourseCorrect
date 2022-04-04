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
			<div class="col">
				<h1>
					My saved plans
					<span class="float-right">
						<button type="button" class="btn btn-secondary mr-2" data-toggle="modal" data-target="#help"><i class="fas fa-question"></i> Help</button><!--
						--><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-plan"><i class="fas fa-plus"></i> Create new plan</button>
					</span>
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
							for ($i = 0; $i<count($plans); $i++) {
								echo "<tr data-plan_id =". $plans[$i]["plan_id"].">";
								echo "<td><span>" . $plans[$i]["plan_title"] ."</span><span>" . planStatusToHTML($plans[$i]["plan_status"]) . "</span></td>";
								echo "<td>" . $plans[$i]["major"]  . " " . $plans[$i]["year"] . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($plans[$i]["created_ts"])) . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($plans[$i]["modified_ts"])) . "</td>";
								echo '<td class="text-nowrap">';
								echo '<a href="../edit?plan=' . $plans[$i]["plan_id"] . '" class="text-dark" title="Edit"><i class="fas fa-edit"></i></a>';
								echo '<a data-toggle="modal" data-target="#duplicate-plan" class="text-dark" title="Duplicate"><i class="fas fa-copy ml-3"></i></a>'; // TODO: Create copy of plan (either one click or a modal for new name)
								echo '<i class="fas fa-trash ml-3" data-plan_id="'. $plans[$i]["plan_id"].'" title="Delete"></i>'; // TODO: Display a delete confirmation modal
								echo "</td>";
								echo "</tr>";	
							}
						?>
					</tbody>
				</table>
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
	
	<div class="modal fade" id="help" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="helpModalLabel">Help</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" style="white-space: pre-wrap"><?=$db->query("SELECT text FROM help_text WHERE id='PlanListHelp'")[0]["text"]?></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="duplicate-plan" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="duplicateModalLabel">Duplicate Plan</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="duplication_form" method="POST" action="dup.php">
					<div class="modal-body">
						<div class="form-group">
							<label for="name_change_field">Plan Name: </label>
							<input type="text" data-original_plan_id="" id="name_change_field" name="name_change_field" class="form-control">
							<input type="hidden" id="og_plan_id" name="og_plan_id">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-success">Duplicate</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script>
		$('body').tooltip({selector: '[title]'});
		$("#duplicate-plan").on("show.bs.modal", e=>{
			let linkClicked = e.relatedTarget;
			let cells = linkClicked.parentElement.parentElement.childNodes;
			let target_element = document.getElementById("name_change_field")
			target_element.value = cells[0].childNodes[0].innerText+" (copy)"; //Accesses the first <td> in the appropriate <tr>, selects the first span containing the plan name and pulls the plan name using innerText.
			target_element.dataset.original_plan_id = linkClicked.parentElement.parentElement.dataset.plan_id; //Retrieves the original plan id from the data-plan_id tag in the appropriate <tr>.
			document.getElementById("og_plan_id").value = linkClicked.parentElement.parentElement.dataset.plan_id;
		});
		// document.getElementById("duplication_form").addEventListener("submit", dupSendID());
		// function dupSendID()
		// {
		// 	let og_plan_id = document.getElementById("name_change_field").dataset.original_plan_id;
		// 	alert(og_plan_id);
		// }
	</script>
</body>
</html>