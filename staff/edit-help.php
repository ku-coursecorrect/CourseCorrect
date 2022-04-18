<!DOCTYPE html>
<?php
	require_once "../common.php";
	require_staff();
	
	if (isset($_POST["id"])) {
		// TODO: Check this worked, display confirmation/error
		$db->query("UPDATE help_text SET description = ?, text = ? WHERE id = ?",
			[$_POST["description"], $_POST["text"], $_POST["id"]]);
			
		// Reload the page as a GET request (instead of a POST request)
		header("Location: edit-help.php");
		exit();
	}
?>
<html lang="en">
<head>
    <title>CourseCorrect</title>
    <meta charset="utf-8">
	<link rel="icon" href="../favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../libs/bootstrap.min.css">
	<script src="../libs/jquery.slim.min.js"></script>
	<script src="../libs/popper.min.js"></script>
	<script src="../libs/bootstrap.min.js"></script>
	<link rel="stylesheet" href="../libs/fontawesome.min.css">
	<link rel="stylesheet" href="../common.css">
	<script src="../common.js"></script>
</head>
<body>
	<?php display_navbar(true); ?>
    <div class="container">
		<div class="row">
			<div class="col">
				<h1>
					Edit help text
				</h1>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>ID</th><th>Description</th><th>Text</th><th>Modified</th><th></th>
						</tr>
					</thead>
					<tbody>
						<?php
							require_once "../common.php";
							
							$texts = $db->query("SELECT id, description, text, modified_ts 
												FROM help_text
												ORDER BY id DESC");
							
							foreach ($texts as $text) {
								echo "<tr>";
								echo "<td>" . $text["id"]. "</td>";
								echo "<td style='white-space: pre-wrap'>" . $text["description"] . "</td>";
								echo "<td style='white-space: pre-wrap'>" . $text["text"] . "</td>";
								echo "<td>" . date(DATE_FORMAT, strtotime($text["modified_ts"])) . "</td>";
								echo '<td class="text-nowrap">';
								echo '<a data-toggle="modal" data-target="#edit-text" class="text-dark" title="Edit"><i class="fas fa-edit"></i></a>';
								echo "</td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php display_footer(); ?>
	
	<!-- Modal -->
	<div class="modal fade" id="edit-text" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalLabel">Edit help text</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="POST" action="edit-help.php">
					<div class="modal-body">
							<div class="form-group">
								<label for="id">ID:</label>
								<input type="text" id="id" name="id" class="form-control" readonly><!-- disabled would not get submitted -->
							</div>
							<div class="form-group">
								<label for="description">Description (for your reference):</label>
								<textarea id="description" name="description" class="form-control"></textarea>
							</div>
							<div class="form-group">
								<label for="text">Text (visible to students):</label>
								<textarea id="text" name="text" class="form-control"></textarea>
							</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-success">Save changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script>
		$("body").tooltip({selector: "[title]"});
		$("#edit-text").on("show.bs.modal", e => {
			let linkClicked = e.relatedTarget;
			let cells = linkClicked.parentElement.parentElement.childNodes;

			document.getElementById("id").value = cells[0].innerText;
			document.getElementById("description").value = cells[1].innerText;
			document.getElementById("text").value = cells[2].innerText;
		});
	</script>
</body>
</html>