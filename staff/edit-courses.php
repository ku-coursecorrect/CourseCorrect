<!DOCTYPE html>
<?php
	require_once "../common.php";
	require_once "course-common.php";
	require_staff();
?>
<html lang="en">
<head>
    <title>Staff - CourseCorrect</title>
    <meta charset="utf-8">
	<link rel="icon" href="../favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../libs/bootstrap.min.css">
	<script src="../libs/jquery.slim.min.js"></script>
	<script src="../libs/popper.min.js"></script>
	<script src="../libs/bootstrap.min.js"></script>
	<link rel="stylesheet" href="../libs/fontawesome.min.css">
	<script src="edit-course.js"></script>
	<style>
		.form-control::placeholder {
			color: #999;
		}
	</style>
</head>
<body>
	<?php display_navbar(); ?>
    <div class="container">
		<div class="row">
			<div class="col-lg-16">
				<h1>
					Edit Courses
					<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#edit-course" onclick="populateModal(this)"><i class="fas fa-plus"></i> Add new course</button>
				</h1>
				<div class="input-group mb-3">
				<div class="input-group-prepend">
					<span class="input-group-text" id="basic-addon1"><i class="fas fa-filter"></i></span>
				</div>
				<input type="text" id="filterTableInput" oninput="waitFilter()" class="form-control" placeholder="Filter Courses" aria-label="filter" aria-describedby="basic-addon1">
				<span class="input-group-append">
				<button class="btn bg-transparent" type="button" style="margin-left: -40px; z-index: 100;" onclick="let input = document.getElementById('filterTableInput'); input.value = ''; waitFilter(); input.focus();">
					<i class="fa fa-times"></i>
				</button>
				</span>	
				</div>
				<table class="table table-striped" id="classTable">
					<?php
						$course_codes = [];
						$TABLE_FORMAT = [
							"Course Number" => fn($course) => $course["course_code"],
							"Title" => fn($course) => $course["title"],
							"Requisites" => function($course) {
								global $course_codes;
								if (isset($course["requisites"])) {
									$req_codes = [];
									foreach ($course["requisites"] as $req) {
										array_push($req_codes, ($req["co_req"] ? "Coreq: " : "Prereq: ") . $course_codes[$req["dependent_id"]]);
									}
									echo implode("<br>", $req_codes);
								}
							},
							"Description" => function($course) {
								$MINLEN = 20;
								$desc = $course["description"];
								$descSanitized = filter_var($desc, FILTER_SANITIZE_STRING);
								echo "<span class=expand data-toggle=tooltip data-placement=auto title='Click to expand' onclick='expandText(event, \"$descSanitized\", true)' style='font-style:italic;'>" . substr($desc, 0, $MINLEN) . "...</span>";
							},
							"Credit Hours" => function($course) {
								if ($course["min_hours"] == $course["max_hours"])
								{
									echo $course["min_hours"], "    ";
								} else {
									echo $course["min_hours"], ' - ', $course["max_hours"];
								}
							},
							"Semester" => function($course) {
								$semesters = [];
								if ($course["f_fall"]) {
									array_push($semesters, "Fall");
								}
								if ($course["f_spring"]) {
									array_push($semesters, "Spring");
								}
								if ($course["f_summer"]) {
									array_push($semesters, "Summer");
								}
								echo implode(", ", $semesters);
							}
						];

						echo '<thead style="position: sticky; inset-block-start: 0; top: -2px; background: white; box-shadow: inset 0 -2px 0 #ccc;"><tr>';
						foreach(array_keys($TABLE_FORMAT) as $field){
							echo '<th>', $field, '</th>';
						}
						echo '<th></th>'; // Extra column for buttons 
						echo '</tr></thead><tbody>';

						$courses = $db->query("SELECT * FROM course;");

						// Obtain and group requisites by course
						$requisites = getReqs();
						
						foreach($courses as $course){
							$course_codes[$course["course_id"]] = $course["course_code"]; 
						}
						foreach($courses as $course){
							$course["requisites"] = $requisites[$course["course_id"]];

							echo '<tr>';
							foreach($TABLE_FORMAT as $field_format) {
								echo '<td>', $field_format($course), '</td>';
							}
							echo '<td class="text-nowrap"><a onclick="populateModal(this)" data-toggle="modal" data-target="#edit-course"><i class="fas fa-edit ml-3"></i></a><a onclick="deleteCourse(this)" data-toggle="modal" data-target="#delete-course"><i class="fas fa-trash ml-3"></i></a></td>';
							echo '</tr>';
						}

						echo '</tbody>';
					?>	
				</table>
			</div>
		</div>
	</div>
	<div class='modal' id='edit-course' tabindex='-1' aria-hidden='true'> <!-- Making this modal a modal fade class breaks the close button for some reason :) -->
	</div>
	<div class="modal" id='delete-course' tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title">Delete Course</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<p>Are you sure you want to delete this course?</p>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary">Delete course</button>
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
		</div>
		</div>
	</div>
	</div>
</body>
</html>