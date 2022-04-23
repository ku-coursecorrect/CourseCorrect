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
	<link rel="stylesheet" href="../common.css">
	<link rel="stylesheet" href="../libs/fontawesome.min.css">
</head>
<body>
	<?php display_navbar(true); ?>
    <div class="container">
		<div class="row">
			<div class="col-12">
				<h1>CourseCorrect Staff</h1>
			</div>
			<div class="col-lg-8 col-xl-9">
				<div class="card mb-3">
					<div class="card-body">
						<h2>Lookup student plans</h2>
						<form method="POST" action="search.php">
							<div class="form-group">
								<label>Student IDs</label>
								<textarea id="stu_id_list" name="stu_id_list" class="form-control" placeholder="3011111, 3022222, 3033333, ..."></textarea>
							</div>
							<div class="form-group">
								<label>Filter plans by name (optional)</label>
								<input id="search_term_list" name="search_term_list" type="text" class="form-control" placeholder="EECS 101">
							</div>
							<button type="submit" class="btn btn-success"><i class="fas fa-search"></i> Search</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-xl-3">
				<div class="card mb-3">
					<div class="card-body">
						<h2>Create plans</h2>
						<label>The student homepage</label><br>
						<a href="../list"><input type="button" class="btn btn-primary" value="Plan list"></a>
					</div>
				</div>
			</div>
			<div class="col-12">
				<div class="card mb-3">
					<div class="card-body">
						<h2>Administration</h2>
						<a href="edit-users.php"><button type="button" class="btn btn-danger"><i class="fas fa-user"></i> Edit staff users</button></a>
						<a href="edit-degrees.php"><button type="button" class="btn btn-danger"><i class="fas fa-graduation-cap"></i> Edit degrees</button></a>
						<a href="edit-courses.php"><button type="button" class="btn btn-danger"><i class="fas fa-book"></i> Edit courses</button></a>
						<a href="edit-help.php"><button type="button" class="btn btn-danger"><i class="fas fa-question"></i> Edit text and links</button></a>
						<a href="view-errors.php"><button type="button" class="btn btn-primary"><i class="fas fa-exclamation"></i> View error logs</button></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php display_footer(); ?>
</body>
</html>