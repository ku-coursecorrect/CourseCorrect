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
					<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#edit-course"><i class="fas fa-plus"></i> Add new course</button>
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
	<!-- Modal -->
	<div class="modal fade" id="edit-course" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editCourse">Edit Course</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="POST" action="edit-course.php">
					<div class="modal-body">
						<div class="form-group">
							<label for="code"><b>Course Code</b></label>
							<input type="text" id="code" name="code" class="form-control" placeholder="EECS 268">
						</div>
						<div class="form-group">
							<label for="title"><b>Title</b></label>
							<input type="text" id="title" name="title" class="form-control" placeholder="Programming II">
						</div>
						<div class="form-group">
							<label for="description"><b>Description</b></label>
							<textarea type="text" id="description" name="description" class="form-control" placeholder="This course continues developing problem solving techniques by focusing on the imperative and object-oriented styles using Abstract Data Types."></textarea>
						</div>
						<div class="container"><div class="row">
							<div class="col-md-auto">
								<label for="credits"><b>Credit Hours</b></label>
								<div class="input-group" id="credits">
									<input type="number" id="credits_min" name="credits_min" class="form-control" placeholder="1" max=1000 min="-1000"/>
									<span class="input-group-text" id="credits_max_separator" style="display:none">-</span>
									<input type="number" id="credits_max" name="credits_max" style="display:none" class="form-control" max="1000" min="-1000" placeholder="max creds"/>
									<button type="button" class="btn btn-outline-secondary" data-toggle="button" aria-pressed="false" autocomplete="off" onclick="toggleCredits(this)">Variable Credits</button>
								</div>
							</div>
							<div class="col-md-auto ml-auto">
								<label for="semesters"><b>Semesters</b></label>
								<div id="semesters">
									<div class="input-group">
										<button class="btn btn-outline-primary" data-toggle="button" aria-pressed="false" type="button" id="springcheck">Spring</button>
										<span style="padding-left:5px; padding-right:5px;">
											<button class="btn btn-outline-primary" data-toggle="button" aria-pressed="false" type="button" id="summercheck">Summer</button>
										</span>
										<button class="btn btn-outline-primary" data-toggle="button" aria-pressed="false" type="button" id="fallcheck">Fall</button>
									</div>
								</div>
							</div>
						</div>
						<label for="requisites" style="padding-top:15px"><b>Requisites</b></label>
						<div id="requisites">
							<table class="table table-striped" id="reqs-table">
								<thead>
									<tr>
										<th>Course Code</th><th>Requisite</th><th>Start Semester</th><th>End Semester</th><th></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<input type="text" id="reqCode1" class="form-control" placeholder="EECS 168"/>
										</td>
										<td><button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" value="prereq" id="reqDrop">Prerequisite</button>
											<div class="dropdown-menu">
											<a class="dropdown-item" selected value="prereq" onclick="dropdownSelect(this)">Prerequisite</a>
											<a class="dropdown-item" value="coreq" onclick="dropdownSelect(this)">Corequisite</a>
											</div>
										</td>
										<td>
											<div class="input-group"  data-toggle=tooltip data-placement=auto title='The first semester for which this requisite is in effect for this course'>
												<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" value="none" id="startSem1">None</button>
												<div class="dropdown-menu">
													<a class="dropdown-item" selected value="none" onclick="dropdownSelect(this)">None</a>
													<a class="dropdown-item" value="spring" onclick="dropdownSelect(this)">Spring</a>
													<a class="dropdown-item" value="summer" onclick="dropdownSelect(this)">Summer</a>
													<a class="dropdown-item" value="fall" onclick="dropdownSelect(this)">Fall</a>
												</div>
												<div class="w-25" style="padding-left:4px">
													<input type="text" id="startYear1" class="form-control" placeholder="year" style="padding:0px; text-align:center" maxlength="4"/>
												</div>
											</div>
										</td>
										<td>
											<div class="input-group" data-toggle=tooltip data-placement=auto title='The final semester for which this requisite is in effect for this course'>
												<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" value="none" id="endSem1">None</button>
												<div class="dropdown-menu">
													<a class="dropdown-item" selected value="none" onclick="dropdownSelect(this)">None</a>
													<a class="dropdown-item" value="spring" onclick="dropdownSelect(this)">Spring</a>
													<a class="dropdown-item" value="summer" onclick="dropdownSelect(this)">Summer</a>
													<a class="dropdown-item" value="fall" onclick="dropdownSelect(this)">Fall</a>
												</div>
												<div class="w-25" style="padding-left:4px">
													<input type="text" id="endYear1" class="form-control" placeholder="year" style="padding:0px; text-align:center" maxlength="4"/>
												</div>
											</div>
										</td>
										<td>
											<i class="fas fa-minus ml-3" onclick="removeReq(this)"></i>
										</td>
									</tr>
								</tbody>
							</table>
							<button type="button" class="btn btn-secondary float-right" onclick="addReq()"><i class="fas fa-plus"></i> Add requisite</button>
						</div>
					</div>
					<div class="modal-footer float-left" style="padding-top:15px">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-success">Create</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>