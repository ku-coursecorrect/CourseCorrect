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
			display_navbar();
            $course_arr = $_POST['course_list_box'];
            $degree_name = $_POST['name'];
            $degree_year = $_POST['year'];
			//Time to do the sql queries
			include_once "degree-func.php";
			send_course($degree_name, $degree_year, $course_arr);
		?>

		<div class="container">
			<div class="row">

                <div class="col-lg-12">
                    <button type="button" class="btn-block" id="return_btn">Return to Edit Degrees Page</button>
                    <div class="text-center">
                        <?php
                            echo "Degree Name: " . $degree_name;
                            echo "<br>";
                            echo "Degree Year: " . $degree_year;
                            echo "<br>";
                        ?>
                    </div>
                    <br>
                    <ul class = "list-group">
                        <?php
                            //check the courses in the db and see if we can get the name
                            foreach($course_arr as $row){
                                echo "<li class='list-group-item'>" . $row . "</li>";
                            }
                        ?>
                    </ul>
                </div>
                <script>
                    $(return_btn).on('click', function(){
                            window.location.href = "edit-degrees.php";
                    })
                </script>
			</div>
		</div>
	</body>

</html>
