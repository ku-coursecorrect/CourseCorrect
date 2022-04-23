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
        .text-md-center {
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
			//Time to do the sql queries
			include_once "degree-func.php";
            $degree_id = $_POST["degree_id"];
            $m_y = get_major_and_year($degree_id);
			$row_my = $m_y[0];
			$degree_name = $row_my["major"];
			$degree_year = $row_my["year"];
		?>

		<div class="container">
			<div class="row">

                <div class="col-lg-12 text-center">
                    <button type="button" class="btn btn-danger" id="delete_btn">DELETE THIS DEGREE, NO TAKE BACKS!</button>
                    <div class="text-md-center">
                        <?php
                            echo "Degree Name: " . $degree_name;
                            echo "<br>";
                            echo "Degree Year: " . $degree_year;
                            echo "<br>";
                        ?>
                    </div>
                    <button type="button" class="btn-block" id="return_btn">NEVERMIND GO BACK, DON'T DELETE THIS DEGREE PLEASE!</button>
                    <br>
                    <ul class = "list-group">
                        <?php
                            include_once "degree-func.php";
                            print_degree_list($degree_id);
                        ?>
                    </ul>
                </div>
                <script>
                    $(delete_btn).on('click', function(){
                        var url = 'degree-deleted.php';
                        var form = $('<form action="' + url + '" method="post">' +
                          '<input type="text" name="degree_id" value="' + <?=$degree_id?> + '" />' +
                          '</form>');
                        $('body').append(form);
                        form.submit();
                    })
                    $(return_btn).on('click', function(){
                            window.location.href = "edit-degrees.php";
                    })
                </script>
			</div>
		</div>
	</body>

</html>
