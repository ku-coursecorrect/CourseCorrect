<?php
    require_once "../../common.php";
    require_staff();
    header('Content-type: application/json');
    $course_id = $_GET["course_id"];
    unset($_GET['course_id']);
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