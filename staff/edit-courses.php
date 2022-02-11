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
	<script>
		function filterTable() {
			let input = document.getElementById("filterTableInput");
			let filter = input.value.toUpperCase();
			let table = document.getElementById("classTable");
			let rows = table.getElementsByTagName("tr");

			for (let row of rows)
			{
				if (row.cells[0].nodeName == "TH") {
					continue;
				}
				for (let column of row.cells) {
					column.innerHTML = column.innerText; // Clean old highlights first
				}
				let found = false;
				for (let column of row.cells) {
					let pos = column.innerText.toUpperCase().indexOf(filter);
					if (pos != -1) {
						highlightWord(column, pos, pos+filter.length);
						found = true;
						break;
					}
				}
				if (!found) {
					row.style.display = "none";
				} else {
					row.style.display = "";
				}
			}
			function highlightWord(column, startPos, endPos) {
				column.innerHTML = column.innerHTML.slice(0, startPos) + "<span style='background-color:yellow;'>" + column.innerHTML.slice(startPos, endPos) + "</span>" + column.innerHTML.slice(endPos);
			}
		}
	</script>
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
				<input type="text" id="filterTableInput" oninput="filterTable()" class="form-control" placeholder="Filter Courses" aria-label="filter" aria-describedby="basic-addon1">
				<span class="input-group-append">
				<button class="btn bg-transparent" type="button" style="margin-left: -40px; z-index: 100;" onclick="let input = document.getElementById('filterTableInput'); input.value = ''; filterTable(); input.focus();">
					<i class="fa fa-times"></i>
				</button>
				</span>	
				</div>
				<table class="table table-striped" id="classTable">
					<?php
						$TABLE_FORMAT = [
							"Course Number" => fn($course) => $course["course_code"],
							"Title" => fn($course) => $course["title"],
							"Description" => fn($course) => $course["description"],
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
						foreach($courses as $course){
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