<?php
    require_once "../../common.php";
    require_staff();
    header('Content-type: application/json');
    $course_code = $_GET["course_code"];
    unset($_GET['course_code']);
    $course_id = $db->query("select course_id from course where course_code=?;", [$course_code])[0]["course_id"];
    $dependent_ids = $db->query("SELECT course_id FROM requisite where dependent_id=?;", [$course_id]);
    if (count($dependent_ids) > 0) {
        $dependent_ids = array_map(function($dep) {return $dep["course_id"];}, $dependent_ids);
        $dependent_list = implode(',', array_fill(0, count($dependent_ids), '?'));
        $dependent_codes = $db->query("SELECT course_code FROM course where course_id in (" . $dependent_list . ");", $dependent_ids);
        $dependent_codes = array_map(function($dep) {return $dep["course_code"];}, $dependent_codes);
        echo json_encode($dependent_codes);
    } else {
        echo json_encode([]);
    }
?>