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
					Edit degrees
					<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#create-plan"><i class="fas fa-plus"></i> Add new degree</button>
				</h1>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Degree</th><th>Year</th><th>Created</th><th>Modified</th><th></th>
						</tr>
					</thead>

					<tbody>
						<?php
						 	include_once "degree-func.php";
							print_degree();
						?>
						<script>
							function degree_edit(degree_id){
								var url = 'edit-degree.php';
								var form = $('<form action="' + url + '" method="post">' +
								  '<input type="text" name="degree_id" value="' + degree_id + '" />' +
								  '</form>');
								$('body').append(form);
								form.submit();
							}

							function degree_trash(degree_id){
								alert(degree_id);
							}
						</script>
					</tbody>
				</table>
			</div>
			<div class="col-lg-4">
				<p>
					<b>Help and Assisstance</b>
				</p>
				<p>
					1) Before adding any new degree, check if all courses have been created and are in the database.
				</p>
				<p>
					2) Check the credit hours expected per semester, verify if the suggested classes are valid with prerequisites and corequisites.
				</p>
				<p>
					3) For technical help, contact coursecorrect-invalid-email@ku.edu
				</p>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="create-plan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Create new degree</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="POST" action="create-degree.php">
					<div class="modal-body">
						<div class="form-group">
							<label for="name">Enter degree name:</label>
							<input type="text" id="name" name="name" class="form-control" placeholder="Computer Science">
						</div>
						<div class="form-group">
							<label for="year">Enter handbook year:</label>
							<input type="text" id="year" name="year" class="form-control" placeholder="2021" value="2021">
						</div>
						<div class="form-group form-check">
							<input type="checkbox" class="form-check-input" id="copy">
							<label class="form-check-label" for="copy">Copy from previous year</label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-success">Create</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
