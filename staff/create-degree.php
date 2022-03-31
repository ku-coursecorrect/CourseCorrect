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
	</head>

	<body>
		<?php display_navbar(); ?>
	    <div class="container">
			<form>
	            <div class= "form-group">
	                <select multiple id="example" name="course_list_box" size = 20>
						<?php
						 	include_once "degree-func.php";
							print_course();
						?>
	            </div>
	            <button type="submit" class="btn btn-default">Submit</button>
	        </form>

		</div>
		<script>
	        var listbox = $('select[name="course_list_box"]').bootstrapDualListbox();
			<!--TODO -->
			$("#demoform").submit(function() {
				alert($('[name="course_list_box"]').val());
				return false;
			});
	    </script>
	</body>

</html>
