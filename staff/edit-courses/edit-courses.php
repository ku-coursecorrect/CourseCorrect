<!DOCTYPE html>
<?php
	require_once "../../common.php";
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
    <link rel="stylesheet" href="../../libs/bootstrap.min.css">
    <link rel="stylesheet" href="course.css">
	<script src="../../libs/jquery.slim.min.js"></script>
	<script src="../../libs/popper.min.js"></script>
	<script src="../../libs/bootstrap.min.js"></script>
	<script src="../../libs/autoComplete.min.js"></script>
	<script src="../../libs/readmore.min.js"></script>
	<script src="../../libs/mark.min.js"></script>
	<link rel="stylesheet" href="../../libs/autoComplete.02.css">
	<link rel="stylesheet" href="../../libs/fontawesome.min.css">
	<link rel="stylesheet" href="../../libs/bootstrap-icons.css">
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
							"Course Number" => function($course) {
								echo $course["course_code"];
							},
							"Title" => function($course) {
								echo $course["title"];
							},
							"Description" => function($course) {
								$MINLEN = 20;
								$desc = $course["description"];
								echo "<article class='desc' style='overflow:hidden'>" . $course["description"] . "</article>";
							},
							"Requisites" => function($course) {
								global $course_codes;
								if (isset($course["requisites"])) {
									foreach ($course["requisites"] as $req) {
										echo '<div class="input-group style="margin: 0px" data-toggle=tooltip data-placement=auto title="' . ($req["co_req"] ? "Corequisite" : "Prequisite") . '">';
										echo '<div class="input-group-prepend">';
										echo '<span class="input-group-text" id="basic-addon1">';
										echo '<b>' . ($req["co_req"] ? "<i class='bi-arrow-left-right'></i>" : "<i class='bi-arrow-right-circle'></i>") . '</b>';
										echo "</span>";
										echo "</div>";
										echo '<input type="text" class="form-control" disabled style="padding: 0px; text-align:center; background: #fff" value="' . $course_codes[$req["dependent_id"]] . '">';
										echo '</div>';
									}
								}
							},
							"Semester" => function($course) {
								$semesters = [];
								echo "<ul class='list-group'>";
								if ($course["f_fall"]) {
									echo "<li class='list-group-item' style='padding: 3px 5px; text-align:center; background:#EDE6DE'>Fall</li>";
								}
								if ($course["f_spring"]) {
									echo "<li class='list-group-item' style='padding: 3px 5px; text-align:center; background:#E5EDDE'>Spring</li>";
								}
								if ($course["f_summer"]) {
									echo "<li class='list-group-item' style='padding: 3px 5px; text-align:center; background:#EDEDDE'>Summer</li>";
								}
								echo "</ul>";
							},
							"ULE Setting" => function($course) {
								echo ULE_OPTIONS[$course["f_ule"]];
							},
							"Credit Hours" => function($course) {
								echo $course["hours"];
							}
						];
						$TABLE_HEADER_FORMAT = [
							"Requisites" => function($field) {
								echo '<th style="width: 13%">', $field, '</th>';
							}
						];

						echo '<thead style="position: sticky; z-index: 1000; inset-block-start: 0; top: -2px; background: white; box-shadow: inset 0 -2px 0 #ccc;"><tr>';
						foreach(array_keys($TABLE_FORMAT) as $field){
							if (array_key_exists($field, $TABLE_HEADER_FORMAT)) {
								$TABLE_HEADER_FORMAT[$field]($field);
							} else {
								echo '<th>', $field, '</th>';
							}
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
							echo '<td class="text-nowrap"><a onclick="populateModal(this)" data-toggle="modal" data-target="#edit-course"><i class="fas fa-edit ml-3"></i></a><a onclick="deleteModal(this)" data-toggle="modal" data-target="#delete-course"><i class="fas fa-trash ml-3"></i></a></td>';
							echo '</tr>';
						}

						echo '</tbody>';
					?>	
				</table>
			</div>
		</div>
	</div>
	<div class='modal' data-backdrop="static" id='edit-course' tabindex='-1' aria-hidden='true'> <!-- Making this modal a modal fade class breaks the close button for some reason :) -->
	</div>
	<div class="modal" id='delete-course' tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title">Delete <span id="delete-subtitle"></span> </h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<p>Are you sure you want to delete this course?</p>
			<p id="dependents"></p>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="button" class="btn btn-primary" onclick="deleteCourse(this)">Delete course</button>
		</div>
		</div>
	</div>
	</div>
</body>
</html>