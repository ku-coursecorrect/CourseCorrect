<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>List Test</title>

    <!-- Bootstrap -->
    <link href="../libs/bootstrap.min.css" rel="stylesheet">
    <link href="../libs/bootstrap-duallistbox.css" rel="stylesheet" />
	<link href="../libs/fontawesome.min.css" rel="stylesheet">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../libs/jquery.slim.min.js"></script>
    <script src="../libs/bootstrap.min.js"></script>
    <script src="../libs/jquery.bootstrap-duallistbox.js"></script>
  </head>

  <body>
    <p><br/></p>
    <div class = "container">
        <form>
            <div class= "form-group">
                <select multiple id="example" name="duallistbox_demo1[]" size = 20>
                    <option>Option 1</option>
                    <option>Option 2</option>
                    <option>Option 3</option>
                    <option>Option 4</option>
                    <option>Option 5</option>
                    <option>Option 6</option>
                    <option>Option 7</option>
                    <option>Option 8</option>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
    <script>
        var demo1 = $('select[name="duallistbox_demo1[]"]').bootstrapDualListbox();
    </script>
  </body>
</html>
