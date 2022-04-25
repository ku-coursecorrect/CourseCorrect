<?php
	require_once "../../common.php";
    require_staff();

    if (array_key_exists('course_id', $_GET)) {
        $course_id = $_GET["course_id"];
	    unset($_GET['course_id']);
        $db->query("delete from requisite where dependent_id=?;", [$course_id]);
        $db->query("delete from requisite where course_id=?;", [$course_id]);
        $db->query("delete from degree_join_course where course_id=?;", [$course_id]);
        $db->query("delete from course where course_id=?;", [$course_id]);
    }

    // Redirect back to the courses page
    header("Location: edit-courses.php");
    exit;
?>