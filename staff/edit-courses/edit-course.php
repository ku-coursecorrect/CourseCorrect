<?php
    require_once "../../common.php";
    require_once "course-common.php";
	$course_id = $_GET['course_id'];
	unset($_GET['course_id']);
    if ($course_id != "New") {
        $course_info = $db->query("SELECT * FROM course where course_id=?;", [$course_id])[0];
        $placeholder_info = $course_info;
        $title = "Edit Course";
    } else {
        $course_info = [
            "course_id" => "",
            "course_code" => "",
            "title" => "",
            "hours" => "",
            "description" => "",
            "f_fall" => "0",
            "f_spring" => "0",
            "f_summer" => "0",
            "f_ule" => "0"
        ];
        $placeholder_info = [
            "course_id" => "",
            "course_code" => "EECS 268",
            "title" => "Programming II",
            "hours" => "1",
            "description" => "This course continues developing problem solving techniques by focusing on the imperative and object-oriented styles using Abstract Data Types.",
            "f_fall" => "0",
            "f_spring" => "1",
            "f_summer" => "0",
            "f_ule" => "0"
        ];
        $title = "Create Course";
    }
?>

<div class='modal-dialog modal-lg'>
	<div class='modal-content'> 
		<div class='modal-header'>
			<h5 class='modal-title' id='editCourse'><?=$title?></h5>
			<button type='button' class='close' data-dismiss='modal' aria-label='Close'>
				<span aria-hidden='true'>&times;</span>
			</button>
		</div>
		<form method='POST' action='commit-course.php' id='edit-course-form' onsubmit="return checkCourseForm()">
			<div class='modal-body'>
				<div class="d-flex justify-content-between">
					<label for='course_code'><b>Course Code</b></label>
					<span>
						<label for='course_id' style="color: #aaa">Course ID</label>
						<input id='course_id' type='text' class="callout" style='border-width: 0px; width: 2em; text-align: center; padding: 2px' name='course_id' value='<?=$course_info['course_id']?>' readonly />
					</span>
				</div>
				<input type='text' id='course_code' name='course_code' class='form-control' placeholder='<?=$placeholder_info['course_code']?>' value='<?=$course_info['course_code']?>'>
				<br>
				<div class='form-group'>
				<label for='title'><b>Title</b></label>
				<input type='text' id='title' name='title' class='form-control' placeholder='<?=$placeholder_info['title']?>' value='<?=$course_info['title']?>'>
				</div>
				<div class='form-group'>
					<label for='description'><b>Description</b></label>
					<textarea type='text' id='description' name='description' class='form-control' rows='5' style='height:100%;' placeholder='<?=$placeholder_info['description']?>'><?=$course_info['description']?></textarea>
				</div>
				<div class='container'>
					<div class='row'>
						<div class='col-md-auto'>
							<label for='credits'><b>Credit Hours</b></label> 
							<input type='number' id='hours' name='hours' class='form-control' placeholder='<?=$placeholder_info['hours']?>' value='<?=$course_info['hours']?>' max=1000 min='-1000'/>
						</div>
						<div class='col'>
							<div class='container'>
								<div class="row justify-content-around">
									<div class='col-md-auto'>
										<label for='uleDrop'><b>ULE Setting</b></label><br>
										<button class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='<?=$course_info['f_ule']?>' id='uleDrop'><?=ULE_OPTIONS[$course_info['f_ule']]?></button>
										<div class='dropdown-menu'>
											<?php 
												foreach(ULE_OPTIONS as $ule_num => $ule_name) {
													echo "<a class='dropdown-item' " . ($ule_num === $course_info['f_ule'] ? 'selected' : '') .  " value='" . $ule_num . "' onclick='dropdownSelect(this);' data-toggle=tooltip data-placement=auto title='" . ULE_HINTS[$ule_num] . "'>" . $ule_name . "</a>";
												}
											?>
										</div>
										<input type='text' id='f_ule' name='f_ule' value='0' hidden />
									</div>
									<div class='col-md-auto'>
										<label for='semesters'><b>Semesters</b></label>
										<div id='semesters'>
											<div class='input-group'>
												<button class='btn btn-outline-primary<?=$course_info['f_spring']==="1" ? ' active' : ''?>' data-toggle='button' type='button' id='springcheck' onclick='addPost(this);'>Spring</button>
												<input type='text' name='f_spring' value='<?=$course_info['f_spring']==="1" ? 'true' : 'false'?>' hidden />
												<span style='padding-left:5px; padding-right:5px;'>
													<button class='btn btn-outline-primary<?=$course_info['f_summer']==="1" ? ' active' : ''?>' data-toggle='button' type='button' id='summercheck' onclick='addPost(this);'>Summer</button>
													<input type='text' name='f_summer' value='<?=$course_info['f_summer']==="1" ? 'true' : 'false'?>' hidden />
												</span>
												<button class='btn btn-outline-primary<?=$course_info['f_fall']==="1" ? ' active' : ''?>' data-toggle='button' type='button' id='fallcheck' onclick='addPost(this);'>Fall</button>
												<input type='text' name='f_fall' value='<?=$course_info['f_fall']==="1" ? 'true' : 'false'?>' hidden />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<label for='requisites' style='padding-top:15px'><b>Requisites</b></label>
				<div id='requisites'>
					<?php 
						$requisites = $db->query("select course.course_id, course.course_code, requisite.co_req, requisite.start_semester, requisite.end_semester from requisite join course on requisite.dependent_id=course.course_id where requisite.course_id=?;", [$course_info['course_id']]);
					?>
					<table class='table table-striped' id='reqs-table'>
						<thead>
							<tr style="display:<?=count($requisites) > 0 ? '' : 'none'?>" id="reqHeaderRow">
								<th>Course Code</th><th>Type</th><th data-toggle=tooltip data-placement=auto title='The first semester for which this requisite is in effect for this course'>Start Semester</th><th  data-toggle=tooltip data-placement=auto title='The final semester for which this requisite is in effect for this course'>End Semester</th><th></th>
							</tr>
						</thead>
						<tbody>
							<?php
								// Fill out course requisites from db
								$req_num = 0;
								foreach($requisites as $req) {
									if ($req["start_semester"] != null) { 
										$req["start_season"] = semester_season(intval($req["start_semester"]));
										$req["start_year"] = semester_year(intval($req["start_semester"]));
									}
									if ($req["end_semester"] != null) { 
										$req["end_season"] = semester_season(intval($req["end_semester"]));
										$req["end_year"] = semester_year(intval($req["end_semester"]));
									}
									$req["req_num"] = strval($req_num);
									include("requisite.php");
									$req_num++;
								}
							?>
						</tbody>
					</table>
					<button type='button' class='btn btn-secondary float-left' onclick='addReq()'><i class='fas fa-plus'></i> Add requisite</button>
					<br>
				</div>
				<input type='text' id='reqs-post' name='requisites' value='[]' hidden />
			</div>
			<div class='modal-footer float-right' style='margin-top:15px'>
				<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
				<button id="form-button-submit" type='button' class='btn btn-success'><?=$title == 'Edit Course' ? 'Save Changes' : 'Create New Course' ?></button>
			</div>
		</form>
	</div>
</div>