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
			display_navbar();
			$degree_id = $_POST['degree_id'];
		?>

		<div class="container">
			<div class="row">

				<form id="demoform" action="save-degree.php" method="post" >
		            <div class= "form-group">
						<select multiple id="example" name="course_list_box[]" size = 20>
						<?php
						 	include_once "degree-func.php";
							print_edit_course($degree_id);
						?>
						</select>
		            </div>
					<br>
					<button class = "btn btn-default btn-block" type="submit" id="submit_btn">Sumbit Degree</button>
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
