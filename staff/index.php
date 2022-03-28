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
				<h1>CourseCorrect Staff</h1>
				<div class="card mb-3">
					<div class="card-body">
						<h2>Admin</h2>
						<a href="edit-users.php"><button type="button" class="btn btn-primary"><i class="fas fa-user"></i> Edit staff users</button></a>
						<a href="edit-degrees.php"><button type="button" class="btn btn-primary"><i class="fas fa-graduation-cap"></i> Edit degrees</button></a>
						<a href="edit-courses/edit-courses.php"><button type="button" class="btn btn-primary"><i class="fas fa-book"></i> Edit courses</button></a>
						<a href="edit-help.php"><button type="button" class="btn btn-primary"><i class="fas fa-question"></i> Edit text and links</button></a>
					</div>
				</div>
				<div class="card mb-3">
					<div class="card-body">
						<h2>Advisor</h2>
						<form method="POST" action="search.php">
							<div class="form-group">
								<label>Student IDs</label>
								<textarea class="form-control" placeholder="3011111, 3022222, 3033333, ..."></textarea>
							</div>
							<div class="form-group">
								<label>Filter plans by name (optional)</label>
								<input type="text" class="form-control" placeholder="EECS 101">
							</div>
							<button type="submit" class="btn btn-success"><i class="fas fa-search"></i> Search</button>
						</form>
					</div>
				</div>
				<div class="card mb-3">
					<div class="card-body">
						<h2>Student</h2>
						<a href="../list"><input type="button" class="btn btn-primary" value="Simulate student experience"></a>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<p class="mt-4">
					This is a place that some help text could be included about what this application is and how students should use it. For example, explanations of what advisors should search for could be placed here.
				</p>
			</div>
		</div>
	</div>
</body>
</html>