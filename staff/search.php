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
        <table class="table table-striped">
            <thead>
                <th scope="col">User Name</th>
                <th scope="col">KUID #</th>
                <th scope="col">Plan Name</th>
                <th scope="col">Plan Status</th>
                <th scope="col">View Plans</th> <!--May be able to comment out this line in the future-->
            </thead>
            <tbody>
                <?php
                    function parseAndCheckStuIds(string $ids) {
                        $id_arr = explode(",",$ids);
                        //TODO: Verify valid input (length and content).
                        return ($id_arr);
                    }
                    function parseAndCheckSearchTerms(string $search_tems) {
                        $arr = explode(",",$search_tems);
                        //TODO: Verify valid input (content).
                        return ($term_arr);
                    }
                    if (!empty($_POST["stu_id_list"]) && !empty($_POST["search_term_list"])){
                        //TODO: Search for plans with a specific keyword in their title restricted to the student's whose IDs were passed in.
                    }
                    elseif (!empty($_POST["stu_id_list"])){
                        $id_arr = parseAndCheckStuIds($_POST["stu_id_list"]);
                        $to_print = $db->query("SELECT user.name, user.kuid, plan.plan_id, plan.plan_title, plan.plan_status 
                                                FROM plan 
                                                INNER JOIN user ON user.kuid IN (" . implode (',', array_fill(0, count($id_arr), '?')) .") 
                                                ORDER BY user.kuid DESC",array_values($id_arr));
                        if (count($to_print)==0){
                            echo '<tr><td colspan="5" class="text-center">No Entries Found.</td></tr>';
                        }
                        else{
                            foreach($to_print as $plan){
                                echo '<tr data-plan_id ='. $plan["plan_id"].'>';
                                echo '<td>' . $plan["name"] . '</td>';
                                echo '<td>' . $plan["kuid"] . '</td>';
                                echo '<td>' . $plan["plan_title"] . '</td>';
                                if ($plan["plan_status"] == 4){
                                    echo '<td><span class="badge badge-success">Complete</span></td>';
                                }
                                else
                                {
                                    echo '<td>' . planStatusToHTML($plan["plan_status"]) . '</td>';
                                }
                                echo '<td class="text-align">';
								echo '<a href="../edit?plan=' . $plan["plan_id"] . '" class="text-dark" title="Edit"><i class="fas fa-edit"></i></a>';
                                echo '</td></tr>';
                            }
                        }
                    }
                    else{
                        echo '<tr><td colspan="5" class="text-center">No IDs Provided.</td></tr>';
                    }

                ?>
            </tbody>
        </table>
	</div>
</body>
</html>