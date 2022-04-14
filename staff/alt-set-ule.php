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

            $course_arr = $_POST['course_list_box'];

            $degree_year = $_POST['year'];
            $degree_name = $_POST['name'];
		?>
        <div class="container">
			<div class="row">
                <div class = col-lg-12>
    				<form id="demoform" action="alt-save-degree.php" method="post" >
    		            <div class= "form-group">
    		                <select multiple id="example" name="ule_list_box[]" size = 20>
    							<?php
    							 	include_once "degree-func.php";
    								print_arr($course_arr);
    							?>
    						</select>
    		            </div>
                        <?php
                        foreach($course_arr as $idx=>$row){
                            $name=htmlentities('course['.$idx.']');
                            $value=htmlentities($row);
                            echo '<input type= "hidden" name="'.$name.'" value="'.$value.'">';
                        }

                        ?>
                        <input type= "hidden" id="hidden_name" name="name" value="<?php echo $_REQUEST['name']; ?>" >
    					<input type= "hidden" id="hidden_year" name="year" value="<?php echo $_REQUEST['year']; ?>" >
    					<br>
    					<button class = "btn btn-default btn-block" type="submit" id="submit_btn">Sumbit ULE Selection and Complete</button>
    		        </form>
    				<script>
    					var listbox = $('select[name="ule_list_box[]"]').bootstrapDualListbox();
    				</script>
                </div>
			</div>
		</div>
	</body>

</html>
