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
            background-color:   #3555f2;
            border: 2px;
            border-color:   black;
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
            $course_ule = $_POST['ule_list_box'];
            $course_all = $_POST['course'];
            $degree_name = $_POST['name'];
            $degree_year = $_POST['year'];
            $course_dif = array_diff($course_ule, $course_all);
		?>

		<div class="container">
			<div class="row">

                <div class="col-lg-8">
                    <div class="text-center">
                        <?php
                            echo "Degree Name: " . $degree_name;
                            echo "<br>";
                            echo "Degree Year: " . $degree_year;
                            echo "<br>";
                        ?>
                    </div>
                    <br>
                    <ul class = "list-group ">
                        <?php
                            foreach($course_all as $row){
                                echo "<li class='list-group-item'>" . $row . "</li>";
                            }
                        ?>
                    </ul>
                    <br>
                    <ul class = "list-group">
                        <?php
                            foreach($course_ule as $row){
                                echo "<li class='list-group-item'>" . $row . "</li>";
                            }
                        ?>
                    </ul>
                </div>
			</div>
		</div>
	</body>

</html>
