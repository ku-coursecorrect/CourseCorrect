<?php
    function refresh_table() {
    	$dbhost = 'localhost';
    	$dbuser = 'root';
    	$dbpass = '';
        $dbname = 'coursecorrect';

        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

        if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
        }

        $table_name = "degree";
        $sql = "SELECT * FROM degree";
        $result = mysqli_query($conn, $sql);

        /*
        while($row = mysqli_fetch_array($result))
        {
            print_r($row);
            echo "\n";
        }
        */
        mysqli_close($conn);
        return $result;
    }

    function refresh_course(){

    }

?>
