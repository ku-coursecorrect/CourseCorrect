<?php
    require_once "../common.php";
    require_once "course-common.php";
    $course_code = $_GET['q'];
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
	$course_info['variable'] = $course_info['min_hours'] !== $course_info['max_hours'];
?>

<div class='modal-dialog modal-lg'>
	<div class='modal-content'>
		<div class='modal-header'>
			<h5 class='modal-title' id='editCourse'><?=$title?></h5>
			<button type='button' class='close' data-dismiss='modal' aria-label='Close'>
				<span aria-hidden='true'>&times;</span>
			</button>
		</div>
		<form method='POST' action='edit-course.php'>
			<div class='modal-body'>
				<div class='form-group'>
					<label for='code'><b>Course Code</b></label>
					<input type='text' id='code' name='code' class='form-control' placeholder='<?=$placeholder_info['course_code']?>' value='<?=$course_info['course_code']?>'>
				</div>
				<div class='form-group'>
					<label for='title'><b>Title</b></label>
					<input type='text' id='title' name='title' class='form-control' placeholder='<?=$placeholder_info['title']?>' value='<?=$course_info['title']?>'>
				</div>
				<div class='form-group'>
					<label for='description'><b>Description</b></label>
					<textarea type='text' id='description' name='description' class='form-control' rows='5' style='height:100%;' placeholder='<?=$placeholder_info['description']?>'><?=$course_info['description']?></textarea>
				</div>
				<div class='container'><div class='row'>
					<div class='col-md-auto'>
						<label for='credits' style='padding-right:25px'><b>Credit Hours</b></label> 
						<button type='button' class='btn btn-outline-secondary<?=$course_info['variable'] ? ' active' : ''?>' data-toggle='button' aria-pressed='<?=$course_info['variable'] ? 'true' : 'false'?>' autocomplete='off' onclick='toggleCredits(this)'>Variable Credits</button>
						<div class='input-group' id='credits'>
							<input type='number' id='credits_min' name='credits_min' class='form-control' placeholder='<?=$placeholder_info['min_hours']?>' value='<?=$course_info['min_hours']?>' max=1000 min='-1000'/>
							<span class='input-group-text' id='credits_max_separator' style='display:<?=$course_info['variable'] ? '' : 'none'?>'>-</span>
							<input type='number' id='credits_max' name='credits_max' style='display:<?=$course_info['variable'] ? '' : 'none'?>' placeholder='<?=$placeholder_info['max_hours']?>' value='<?=$course_info['max_hours']?>' class='form-control' max='1000' min='-1000' placeholder='max creds'/>
						</div>
					</div>
					<div class='col-md-auto ml-auto'>
						<label for='semesters'><b>Semesters</b></label>
						<div id='semesters'>
							<div class='input-group'>
								<button class='btn btn-outline-primary<?=$course_info['f_spring']==="1" ? ' active' : ''?>' data-toggle='button' aria-pressed='false' type='button' id='springcheck'>Spring</button>
								<span style='padding-left:5px; padding-right:5px;'>
									<button class='btn btn-outline-primary<?=$course_info['f_summer']==="1" ? ' active' : ''?>' data-toggle='button' aria-pressed='false' type='button' id='summercheck'>Summer</button>
								</span>
								<button class='btn btn-outline-primary<?=$course_info['f_fall']==="1" ? ' active' : ''?>' data-toggle='button' aria-pressed='false' type='button' id='fallcheck'>Fall</button>
							</div>
						</div>
					</div>
				</div>
				<label for='requisites' style='padding-top:15px'><b>Requisites</b></label>
				<div id='requisites'>
					<table class='table table-striped' id='reqs-table'>
						<thead>
							<tr>
								<th>Course Code</th><th>Requisite</th><th>Start Semester</th><th>End Semester</th><th></th>
							</tr>
						</thead>
						<tbody>
							<?php
								// Fill out course requisites from db
								$requisites = $db->query("select course.course_code, requisite.co_req, requisite.start_semester, requisite.end_semester from requisite join course on requisite.dependent_id=course.course_id where requisite.course_id=?;", [$course_info['course_id']]);
								foreach($requisites as $req) {
									if ($req["start_semester"] != null) { 
										$req["start_season"] = semester_season(intval($req["start_semester"]));
										$req["start_year"] = semester_year(intval($req["start_semester"]));
									}
									if ($req["end_semester"] != null) { 
										$req["end_season"] = semester_season(intval($req["end_semester"]));
										$req["end_year"] = semester_year(intval($req["end_semester"]));
									}
									include("requisite.php");
								}
							?>
						</tbody>
					</table>
					<button type='button' class='btn btn-secondary float-right' onclick='addReq()'><i class='fas fa-plus'></i> Add requisite</button>
				</div>
			</div>
			<div class='modal-footer float-left' style='padding-top:15px'>
				<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
				<button type='submit' class='btn btn-success'><?=$title?></button>
			</div>
		</form>
	</div>
</div>