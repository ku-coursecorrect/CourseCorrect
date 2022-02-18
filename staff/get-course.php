<?php
    require_once "../common.php";
    require_once "course-common.php";
    $course_code = substr($_GET['q'], 0, 12); // TODO: SANITIZE????
    if ($course_code != "New") {
        $course_info = $db->query("SELECT * FROM course where course_code=?;", [$course_code])[0];
        $placeholder_info = $course_info;
        $title = "Edit Course";
    } else {
        $course_info = [
            "course_id" => "",
            "course_code" => "",
            "title" => "",
            "min_hours" => "",
            "max_hours" => "",
            "description" => "",
            "f_fall" => "0",
            "f_spring" => "0",
            "f_summer" => "0"
        ];
        $placeholder_info = [
            "course_id" => "",
            "course_code" => "EECS 268",
            "title" => "Programming II",
            "min_hours" => "1",
            "max_hours" => "max creds",
            "description" => "This course continues developing problem solving techniques by focusing on the imperative and object-oriented styles using Abstract Data Types.",
            "f_fall" => "0",
            "f_spring" => "1",
            "f_summer" => "0"
        ];
        $title = "Create Course";
    }

    echo "<template id='req-row'>";
	echo "	<tr id='req-1'>";
	echo "		<td>";
	echo "			<input type='text' id='reqCode' class='form-control' placeholder='EECS 168'/>";
	echo "		</td>";
	echo "		<td><button class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='prereq' id='reqDrop'>Prerequisite</button>";
	echo "			<div class='dropdown-menu'>";
	echo "			<a class='dropdown-item' selected value='prereq' onclick='dropdownSelect(this)'>Prerequisite</a>";
	echo "			<a class='dropdown-item' value='coreq' onclick='dropdownSelect(this)'>Corequisite</a>";
	echo "			</div>";
	echo "		</td>";
	echo "		<td>";
	echo "			<div class='input-group'  data-toggle=tooltip data-placement=auto title='The first semester for which this requisite is in effect for this course'>";
	echo "				<button class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='none' id='startSem'>None</button>";
	echo "				<div class='dropdown-menu'>";
	echo "					<a class='dropdown-item' selected value='none' onclick='dropdownSelect(this)'>None</a>";
	echo "					<a class='dropdown-item' value='spring' onclick='dropdownSelect(this)'>Spring</a>";
	echo "					<a class='dropdown-item' value='summer' onclick='dropdownSelect(this)'>Summer</a>";
	echo "					<a class='dropdown-item' value='fall' onclick='dropdownSelect(this)'>Fall</a>";
	echo "				</div>";
	echo "				<div class='w-25' style='padding-left:4px'>";
	echo "					<input type='text' id='startYear' class='form-control' placeholder='year' style='padding:0px; text-align:center' maxlength='4'/>";
	echo "				</div>";
	echo "			</div>";
	echo "		</td>";
	echo "		<td>";
	echo "			<div class='input-group' data-toggle=tooltip data-placement=auto title='The final semester for which this requisite is in effect for this course'>";
	echo "				<button class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='none' id='endSem'>None</button>";
	echo "				<div class='dropdown-menu'>";
	echo "					<a class='dropdown-item' selected value='none' onclick='dropdownSelect(this)'>None</a>";
	echo "					<a class='dropdown-item' value='spring' onclick='dropdownSelect(this)'>Spring</a>";
	echo "					<a class='dropdown-item' value='summer' onclick='dropdownSelect(this)'>Summer</a>";
	echo "					<a class='dropdown-item' value='fall' onclick='dropdownSelect(this)'>Fall</a>";
	echo "				</div>";
	echo "				<div class='w-25' style='padding-left:4px'>";
	echo "					<input type='text' id='endYear' class='form-control' placeholder='year' style='padding:0px; text-align:center' maxlength='4'/>";
	echo "				</div>";
	echo "			</div>";
	echo "		</td>";
	echo "		<td>";
	echo "			<i class='fas fa-trash ml-3' onclick='removeReq(this)'></i>";
	echo "		</td>";
	echo "	</tr>";
	echo "</template>";

	echo "	<div class='modal-dialog modal-lg'>";
	echo "		<div class='modal-content'>";
	echo "			<div class='modal-header'>";
	echo "				<h5 class='modal-title' id='editCourse'>$title</h5>";
	echo "				<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
	echo "					<span aria-hidden='true'>&times;</span>";
	echo "				</button>";
	echo "			</div>";
	echo "			<form method='POST' action='edit-course.php'>";
	echo "				<div class='modal-body'>";
	echo "					<div class='form-group'>";
	echo "						<label for='code'><b>Course Code</b></label>";
	echo "						<input type='text' id='code' name='code' class='form-control' placeholder='$placeholder_info[course_code]' value='$course_info[course_code]'>";
	echo "					</div>";
	echo "					<div class='form-group'>";
	echo "						<label for='title'><b>Title</b></label>";
	echo "						<input type='text' id='title' name='title' class='form-control' placeholder='$placeholder_info[title]' value='$course_info[title]'>";
	echo "					</div>";
	echo "					<div class='form-group'>";
	echo "						<label for='description'><b>Description</b></label>";
	echo "						<textarea type='text' id='description' name='description' class='form-control' rows='5' style='height:100%;' placeholder='$placeholder_info[description]'>$course_info[description]</textarea>";
	echo "					</div>";
	echo "					<div class='container'><div class='row'>";
	echo "						<div class='col-md-auto'>";
	echo "							<label for='credits' style='padding-right:25px'><b>Credit Hours</b></label> ";
	echo "							<button type='button' class='btn btn-outline-secondary' data-toggle='button' aria-pressed='false' autocomplete='off' onclick='toggleCredits(this)'>Variable Credits</button>";
	echo "							<div class='input-group' id='credits'>";
	echo "								<input type='number' id='credits_min' name='credits_min' class='form-control' placeholder='$placeholder_info[min_hours]' value='$course_info[min_hours]' max=1000 min='-1000'/>";
	echo "								<span class='input-group-text' id='credits_max_separator' style='display:none'>-</span>";
	echo "								<input type='number' id='credits_max' name='credits_max' style='display:none' placeholder='$placeholder_info[max_hours]' value='$course_info[max_hours]' class='form-control' max='1000' min='-1000' placeholder='max creds'/>";
	echo "							</div>";
	echo "						</div>";
	echo "						<div class='col-md-auto ml-auto'>";
	echo "							<label for='semesters'><b>Semesters</b></label>";
	echo "							<div id='semesters'>";
	echo "								<div class='input-group'>";
	echo "									<button class='btn btn-outline-primary' data-toggle='button' aria-pressed='false' type='button' id='springcheck'>Spring</button>";
	echo "									<span style='padding-left:5px; padding-right:5px;'>";
	echo "										<button class='btn btn-outline-primary' data-toggle='button' aria-pressed='false' type='button' id='summercheck'>Summer</button>";
	echo "									</span>";
	echo "									<button class='btn btn-outline-primary' data-toggle='button' aria-pressed='false' type='button' id='fallcheck'>Fall</button>";
	echo "								</div>";
	echo "							</div>";
	echo "						</div>";
	echo "					</div>";
	echo "					<label for='requisites' style='padding-top:15px'><b>Requisites</b></label>";
	echo "					<div id='requisites'>";
	echo "						<table class='table table-striped' id='reqs-table'>";
	echo "							<thead>";
	echo "								<tr>";
	echo "									<th>Course Code</th><th>Requisite</th><th>Start Semester</th><th>End Semester</th><th></th>";
	echo "								</tr>";
	echo "							</thead>";
	echo "							<tbody>";
	echo "								<!-- Template rows go here -->";
	echo "							</tbody>";
	echo "						</table>";
	echo "						<button type='button' class='btn btn-secondary float-right' onclick='addReq()'><i class='fas fa-plus'></i> Add requisite</button>";
	echo "					</div>";
	echo "				</div>";
	echo "				<div class='modal-footer float-left' style='padding-top:15px'>";
	echo "					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>";
	echo "					<button type='submit' class='btn btn-success'>Create</button>";
	echo "				</div>";
	echo "			</form>";
	echo "		</div>";
	echo "	</div>";
	echo "</div>";
?>