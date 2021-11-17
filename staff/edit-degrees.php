<!DOCTYPE html>
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
	<nav class="navbar navbar-light bg-light mb-4">
		<a class="navbar-brand" href="https://ku.edu">
			<img src="../images/KUSig_Horz_Web_Blue.png" height="30" alt="">
		</a>
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="https://eecs.ku.edu">Electrical Engineering and Computer Science</a>
			</li>
		</ul>
	</nav>
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
						<tr>
							<td>Computer Science</td><td>2021</td><td>May 28<sup>th</sup>, 2021</td><td>Jul 28<sup>th</sup>, 2021</td>
							<td class="text-nowrap"><a href="edit-degree.php"><i class="fas fa-edit ml-3"></i></a><i class="fas fa-trash ml-3"></i></td>
						</tr>
						<tr>
							<!-- <i class="fas fa-clone"></i> -->
							<td>IC Biology</td><td>2019</td><td>May 28<sup>th</sup>, 2019</td><td>Jul 28<sup>th</sup>, 2019</td>
							<td class="text-nowrap"><i class="fas fa-edit ml-3"></i><i class="fas fa-trash ml-3"></i></td>
						</tr>
						<tr>
							<td>Computer Engineering</td><td>2019</td><td>May 28<sup>th</sup>, 2019</td><td>Jul 28<sup>th</sup>, 2019</td>
							<td class="text-nowrap"><i class="fas fa-edit ml-3"></i><i class="fas fa-trash ml-3"></i></td>
						</tr>
						<tr>
							<td>Computer Science</td><td>2019</td><td>May 28<sup>th</sup>, 2019</td><td>Jul 28<sup>th</sup>, 2019</td>
							<td class="text-nowrap"><i class="fas fa-edit ml-3"></i><i class="fas fa-trash ml-3"></i></td>
						</tr>
						<tr>
							<td>IC Biology</td><td>2018</td><td>Jun 1<sup>st</sup>, 2018</td><td>Jul 17<sup>th</sup>, 2018</td>
							<td class="text-nowrap"><i class="fas fa-edit ml-3"></i><i class="fas fa-trash ml-3"></i></td>
						</tr>
						<tr>
							<td>Computer Engineering</td><td>2018</td><td>Jun 1<sup>st</sup>, 2018</td><td>Jul 17<sup>th</sup>, 2018</td>
							<td class="text-nowrap"><i class="fas fa-edit ml-3"></i><i class="fas fa-trash ml-3"></i></td>
						</tr>
						<tr>
							<td>Computer Science</td><td>2018</td><td>Jun 1<sup>st</sup>, 2018</td><td>Jul 17<sup>th</sup>, 2018</td>
							<td class="text-nowrap"><i class="fas fa-edit ml-3"></i><i class="fas fa-trash ml-3"></i></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-lg-4">
				<p>
					This is a place that some help text could be included about how to use this page.
				</p>
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ultrices lorem mi, vel dapibus diam posuere eu. Aliquam facilisis iaculis ipsum venenatis venenatis. Phasellus vulputate, ipsum quis mattis viverra, lectus dui sodales libero, id consequat massa justo ut magna. Donec sed ullamcorper metus. Donec lorem mauris, gravida eu pharetra nec, rutrum a arcu. Cras cursus eget nisl id luctus. Pellentesque sit amet sagittis felis.
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