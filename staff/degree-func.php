<?php
    require_once "../common.php";

    function read_degree() {
        global $db;
        $degree_info = $db->query("SELECT * FROM degree;", []);
        return $degree_info;
    }
    function print_degree() {
        $table = read_degree();
        $temp_date = "Oct 23, 2077";
        foreach($table as $row){
            echo "<tr>";

            echo "<td>" .  $row["major"] . "</td>";
            echo "<td>" .  $row["year"] . "</td>";
            echo "<td>" .  date(DATE_FORMAT, strtotime($row["created_ts"])) . "</td>";
            echo "<td>" .  date(DATE_FORMAT, strtotime($row["modified_ts"])). "</td>";
            echo "<td class='text-nowrap'><i class='fas fa-edit ml-3'></i><i class='fas fa-trash ml-3'></i></td>";

            echo "</tr>";
        }
    }



    function refresh_course(){
        $dbhost = 'localhost';
    	$dbuser = 'root';
    	$dbpass = '';
        $dbname = 'coursecorrect';

        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

        if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
        }

        $table_name = "course";
        $sql = "SELECT * FROM course";
        $result = mysqli_query($conn, $sql);

        mysqli_close($conn);
        return $result;
    }

?>
