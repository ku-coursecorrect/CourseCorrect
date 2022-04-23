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
		.text-center {
            background-color:   rgb(0, 81, 186);
            color: white;
            padding: 15px 32 px;
            text-align: center;
            font-size: 16px;
            margin: 4px 2px;
        }
		</style>
	</head>

	<body>
		<?php
			display_navbar(true);
			$degree_id = $_POST['degree_id'];
			//get data
			include_once "degree-func.php";
			$m_y = get_major_and_year($degree_id);
			$row_my = $m_y[0];
			$major = $row_my["major"];
			$year = $row_my["year"];
		?>

		<div class="container">
			<div class="row">
				<form id="demoform" action="save-edit-degree.php" method="post" >
					<div class="text-center">
                        <?php
                            echo "Old Degree Name: " . $major;
                            echo "<br>";
                            echo "Old Degree Year: " . $year;
                            echo "<br>";
                        ?>
                    </div>
					<input type="text" id="hidden_name" name="name" class= "form-control col-lg-8" value="<?= $major;?>" />
					<input type="text" id="hidden_year" name="year" class= "form-control col-lg-8" value="<?= $year;?>" />
		            <div class= "form-group">
						<select multiple id="list" name="course_list_box[]" size = 20>
						<?php
							print_edit_course($degree_id);
						?>
						</select>
		            </div>
					<input type= "hidden" id="hidden_id" name="id" value="<?=$degree_id;?>" >
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
