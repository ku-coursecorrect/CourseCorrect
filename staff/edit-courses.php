<!DOCTYPE html>
<?php
	require_once "../common.php";
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
</head>
<body>
	<?php display_navbar(); ?>
    <div class="container">
		<div class="row">
			<div class="col-lg-16">
				<h1>
					Edit Courses
					<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#create-course"><i class="fas fa-plus"></i> Add new course</button>
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
									echo implode(", ", $req_codes);
								}
							},
							"Description" => function($course) {
								$MINLEN = 40;
								$desc = $course["description"];
								$descSanitized = addslashes($desc);
								echo "<span onclick='expandText(event, \"$descSanitized\", true)' style='display:flex; font-style:italic;'>" . substr($desc, 0, $MINLEN) . "...</span>";
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
						echo '</tr><tbody>';

						$courses = $db->query("SELECT * FROM course;");

						// Obtain and group requisites by course
						$reqs_array = $db->query("SELECT * FROM requisite;");
						$requisites = [];
						foreach($reqs_array as $req) {
							if (!array_key_exists($req["course_id"], $reqs_array)) {
								$requisites[$req["course_id"]] = [];
							}
							array_push($requisites[$req["course_id"]], $req);
						}
						
						foreach($courses as $course){
							$course["requisites"] = $requisites[$course["course_id"]];
							$course_codes[$course["course_id"]] = $course["course_code"];

							echo '<tr>';
							foreach($TABLE_FORMAT as $field_format) {
								echo '<td>', $field_format($course), '</td>';
							}
							echo '<td class="text-nowrap"><a href="edit-course.php"><i class="fas fa-edit ml-3"></i></a><i class="fas fa-trash ml-3"></i></td>';
							echo '</tr>';
						}

						echo '</tbody>';
					?>	
				</table>
			</div>
		</div>
	</div>
</body>
</html>