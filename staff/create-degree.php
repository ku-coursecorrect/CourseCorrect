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

		<!-- Bootstrap -->
	    <link href="../libs/bootstrap.min.css" rel="stylesheet">
	    <link href="../libs/bootstrap-duallistbox.css" rel="stylesheet" />
		<link href="../libs/fontawesome.min.css" rel="stylesheet">

	    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="../libs/jquery.slim.min.js"></script>
	    <script src="../libs/bootstrap.min.js"></script>
	    <script src="../libs/jquery.bootstrap-duallistbox.js"></script>

		<link rel="stylesheet" href="../common.css">
		<style>
		.btn-block {
			background-color: #4CAF50;
			border: none;
			color: white;
			padding: 15px 32px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 16px;
			margin: 4px 2px;
			cursor: pointer;
		}
		</style>
	</head>

	<body>
		<?php
			display_navbar(true);
			include_once "degree-func.php";
			//string, either null or "on"
			$degree_copy = $_REQUEST["copy"];
			$prev_selected = FALSE;
			$prev_id;

			$degree_name = $_REQUEST["name"];
			$degree_year = $_REQUEST["year"];
			if($degree_copy != NULL){
				$prev_selected = TRUE;
				$prev_id = prev_degree($degree_name, $degree_year)[0]["degree_id"];
			}
		?>

		<div class="container">
			<div class="row">

				<form id="demoform" action="save-degree.php" method="post" >
		            <div class= "form-group">
		                <select multiple id="example" name="course_list_box[]" size = 20>
							<?php
								if($prev_selected && $prev_id != NULL){
									print_edit_course($prev_id);
								}
								else{
									print_course();
								}
							?>
						</select>
		            </div>
					<input type= "hidden" id="hidden_name" name="name" value="<?php echo $_REQUEST['name']; ?>" >
					<input type= "hidden" id="hidden_year" name="year" value="<?php echo $_REQUEST['year']; ?>" >
					<br>
					<button class = "btn btn-default btn-block" type="submit" id="submit_btn">Submit Degree</button>
		        </form>
				<script>
					var listbox = $('select[name="course_list_box[]"]').bootstrapDualListbox({
						nonSelectedListLabel: "Unselected Courses",
						selectedListLabel: "Selected Courses"
					});
				</script>
			</div>
		</div>
	</body>

</html>
