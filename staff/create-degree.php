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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../libs/bootstrap.min.css">
	<script src="../libs/jquery.slim.min.js"></script>
	<script src="../libs/popper.min.js"></script>
	<script src="../libs/bootstrap.min.js"></script>
	<link rel="stylesheet" href="../libs/fontawesome.min.css">
</head>

<body>
	<?php display_navbar(); ?>
    <div class="container">
		<div class="row">
			<div class="col-lg-8">
				<h1>
					Create Degree
				</h1>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Course Name</th><th>Title</th><th>PreReq</th><th>CoReq</th><th>Include</th><th></th>
						</tr>
					</thead>
                    <tbody>
                        <?php
						 	include_once "degree-func.php";
							$tables = refresh_course();
							$arrays = $tables->fetch_all(MYSQLI_NUM);
							$arr_count = count($arrays);

							for($i = 0; $i<$arr_count; $i++){
								$arr2 = $arrays[$i];
								$arr_count2 = count($arr2);
								echo "<tr>";
								//J starts at 1 to remove primary id
								//and arr_count2 is -1 to remove description
								for($j = 1; $j<$arr_count2-1; $j++){
									echo "<td>" . $arr2[$j] . "</td>";
								}
								echo "<td>" .  $temp_date . "</td>";
								echo "<td>" .  $temp_date . "</td>";
								echo "<td class='text-nowrap'><i class='fas fa-edit ml-3'></i><i class='fas fa-trash ml-3'></i></td>";
								echo "</tr>";

							}
						?>
                    </tbody>
				</table>
			</div>
		</div>
	</div>
</body>
