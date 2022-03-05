<?php
	require_once "../../common.php";
    require_staff();

    if (array_key_exists('course_code', $_GET)) {
        $course_code = $_GET["course_code"];
        $course_id = $db->query("select course_id from course where course_code=?;", [$course_code])[0]["course_id"];
        $db->query("delete from requisite where dependent_id=?;", [$course_id]);
        $db->query("delete from requisite where course_id=?;", [$course_id]);
        $db->query("delete from course where course_id=?;", [$course_id]);
    }

    // Redirect back to the courses page
    header("Location: edit-courses.php");
    exit;
?>