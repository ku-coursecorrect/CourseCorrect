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
	<style>
		.course {
			width: 90px;
			height: 45px;
			border: 2px outset gray;
			border-radius: 5px;
			display: flex;
			justify-content: center;
			align-items: center;
			text-align: center;
			line-height: 1;
		}
		.course:hover {
			background: yellow;
		}
	</style>
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
					Computer Science 2021 <span class="badge badge-success">Visible</span><!-- TODO: Option to hide from students before ready -->
				</h1>
				<div class="card mb-3">
					<div class="card-body">
						<h2>
							Required courses
							<!--<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#create-plan"><i class="fas fa-plus"></i> Add course</button>-->
						</h2>
						<div class="btn btn-outline-dark" data-toggle="modal" data-target="#edit-course">EECS 101</div>
						<div class="btn btn-outline-dark">EECS 168</div>
						<div class="btn btn-outline-dark">EECS 268</div>
						<div class="btn btn-outline-dark">MATH 125</div>
						<div class="btn btn-outline-dark">MATH 126</div>
						<div class="btn btn-outline-dark">MATH 127</div>
						<div class="btn btn-outline-dark">GE 2.1(1)</div>
						<div class="btn btn-outline-dark">GE 2.1(2)</div>
						<div class="btn btn-outline-dark">GE 3S</div>
						<br>
						Total credit hours: 128
						<hr>
						<div class="form-group">
							<label>Add an existing course:</label>
							<div class="input-group">
								<input type="text" class="form-control" placeholder="EECS 168">
								<div class="input-group-append">
									<input type="button" class="btn btn-primary" value="Add">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-body">
						<h2>
							Senior electives
						</h2>
					</div>
				</div>
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
	<div class="modal fade" id="edit-course" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">EECS 101</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<b>Title:</b> New Student Seminar<br>
					<b>Credit hours:</b> 1<br>
					<b>Semesters offered:</b> Fall<br>
					<b>Prerequisites:</b> None<br>
					<b>Corequisites:</b> MATH 104<br>
					<b>Upper Level Eligibility:</b> Required to obtain ULE
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger">Remove from degree</button>
					<button type="submit" class="btn btn-primary">Edit course</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>