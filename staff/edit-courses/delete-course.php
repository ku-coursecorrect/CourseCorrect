<?php
	require_once "../../common.php";
    require_staff();

    if (array_key_exists('course_code', $_GET)) {
        $course_code = $_GET["course_code"];
        $db->query("delete from course where course_code=?;", [$course_code]);
    }

    // Redirect back to the courses page
    header("Location: edit-courses.php");
    exit;
?>