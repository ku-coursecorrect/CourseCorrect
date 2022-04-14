<?php
    require_once "../common.php";

    function print_degree() {
        global $db;
        $degree_info = $db->query("SELECT * FROM degree;", []);
        foreach($degree_info as $row){
            echo "<tr>";

            echo "<td>" .  $row["major"] . "</td>";
            echo "<td>" .  $row["year"] . "</td>";
            echo "<td>" .  date(DATE_FORMAT, strtotime($row["created_ts"])) . "</td>";
            echo "<td>" .  date(DATE_FORMAT, strtotime($row["modified_ts"])). "</td>";
            echo "<td class='text-nowrap'><i class='fas fa-edit ml-3'></i><i class='fas fa-trash ml-3'></i></td>";

            echo "</tr>";
        }
    }

    function print_course(){
        global $db;
        $course_code = $db->query("SELECT * FROM course;", []);
        foreach($course_code as $row){
            echo "<option value ='" . $row["course_code"] . "' " ."data-hours ='" . $row["hours"];
            echo "'>";
            echo $row["course_code"] .": ". $row["title"];
            echo "</option>";
        }
    }

    function print_arr($arr){
        foreach($arr as $row){
            echo "<option>";
            echo $row;
            echo "</option>";
        }
    }

    function send_course($f_name, $f_year, $f_courses){
        #INSERT INTO `coursecorrect`.`degree` (`major`, `year`) VALUES ('Computer Science', 2021);
        #INSERT INTO `coursecorrect`.`degree_join_course` (`course_id`, `degree_id`) VALUES ('69', '4');
        global $db;

        $table_query = "INSERT INTO `coursecorrect`.`degree` (`major`, `year`) VALUES ('" . $f_name . "', " . $f_year . ");";
        //Now get table id
        $db->query($table_query);
        $degree_id = $db->lastInsertId();
        echo $degree_id;
        foreach($f_courses as $row){
            $course_id_query = "SELECT `course_id` FROM course WHERE `course_code` = '" . $row . "';";
            $course_id_table = $db->query($course_id_query);
            $course_id_row = $course_id_table[0];

            $course_query = "INSERT INTO `coursecorrect`.`degree_join_course` (`course_id`, `degree_id`) VALUES (" . $course_id . ", " . $degree_id . ");";
            $db->query($course_query);
        }
    }
?>
