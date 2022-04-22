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
            echo "<td><button onclick='degree_edit(" . '"' . $row["degree_id"] .'"'. ")' class='btn'><i class='fas fa-edit ml-3'></i></button></td>";
            echo "<td><button onclick='degree_trash(" . '"' . $row["degree_id"] .'"'. ")' class='btn'><i class='fas fa-trash ml-3'></i></button></td>";
            // echo "<td class='text-nowrap'><i class='fas fa-trash ml-3'></i></td>";

            echo "</tr>";
        }
    }

    function print_course(){
        global $db;
        $course_code = $db->query("SELECT * FROM course ORDER BY course_code;", []);
        foreach($course_code as $row){
            //<option value  ='$row["course_code"]' data-hours='$row["hours"]'>
            //$row["course_code"]: $row["title"]
            //</option>
            echo "<option value ='" . $row["course_id"] . "' " ."data-hours ='" . $row["hours"];
            echo "'>";
            echo $row["course_code"] .": ". $row["title"];
            echo "</option>";
        }
    }

    function print_edit_course($degree_id){
        global $db;
        $degree_code_query = "SELECT course.course_id, course.course_code, course.title, course.hours FROM degree_join_course, course WHERE degree_id = ". $degree_id . " AND course.course_id = degree_join_course.course_id;";
        $degree_code = $db->query($degree_code_query, []);

        $course_code_query = "SELECT DISTINCT course.course_id, course.course_code, course.title, course.hours FROM course, (SELECT course_id FROM degree_join_course WHERE degree_id = " . $degree_id . ") AS K WHERE course.course_id != K.course_id;";
        $course_code = $db->query($course_code_query,[]);

        if(empty($degree_code)){
            print_course();
        }
        else{
            //unselected courses
            foreach($course_code as $row){
                echo "<option value ='" . $row["course_id"] . "' " ."data-hours ='" . $row["hours"] . "'";
                echo ">";
                echo $row["course_code"] .": ". $row["title"];
                echo "</option>";
            }
            //selected courses
            foreach($degree_code as $row){
                echo "<option value ='" . $row["course_id"] . "' " ."data-hours ='" . $row["hours"] . "'";
                echo " selected = 'selected'";
                echo ">";
                echo $row["course_code"] .": ". $row["title"];
                echo "</option>";
            }
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
        global $db;

        $table_query = "INSERT INTO `coursecorrect`.`degree` (`major`, `year`) VALUES ('" . $f_name . "', " . $f_year . ");";
        // Now get table id
        $db->query($table_query);
        $degree_id = $db->lastInsertId();
        foreach($f_courses as $row){
            $course_id = $row;

            $course_query = "INSERT INTO `coursecorrect`.`degree_join_course` (`course_id`, `degree_id`) VALUES (" . $course_id . ", " . $degree_id . ");";
            $db->query($course_query);
        }
    }

    function update_course($f_id, $f_courses){
        global $db;

        foreach($f_courses as $row){
            $course_id = $row;

            $course_query = "INSERT INTO `coursecorrect`.`degree_join_course` (`course_id`, `degree_id`) VALUES (" . $course_id . ", " . $f_id . ");";
            $db->query($course_query);
        }
    }


    function translate_id_to_code($f_courses){
        global $db;

        foreach($f_courses as $row){
            $code_query = "SELECT course_code FROM course WHERE course_id = '" . $row . "';";
            $code_table = $db->query($code_query);
            $code_row = $code_table[0];
            $code = $code_row['course_code'];
            echo "<li class='list-group-item'>" . $code . "</li>";
        }
    }

    function get_major_and_year($f_id){
        global $db;
        // SELECT major, year
        // FROM degree
        // WHERE degree_id = 1;
        $db_query = "SELECT major, year FROM degree WHERE degree_id =" . $f_id . ";";
        return $db->query($db_query);
    }

    function wipe_degree_course($degree_id){
        global $db;

        $db_query = "DELETE FROM degree_join_course WHERE degree_id = " . $degree_id . ";";
        $db->query($db_query);
    }
?>
