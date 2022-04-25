<?php
    require_once "../../common.php";
    require_staff();
    header('Content-type: application/json');
    $course_id = $_GET["course_id"];
    unset($_GET['course_id']);
    $degrees = $db->query("SELECT major, year FROM degree_join_course as djc join degree as deg on djc.degree_id=deg.degree_id where course_id=? AND deg.deleted_ts IS NULL;", [$course_id]);
    if (count($degrees) > 0) {
        echo json_encode($degrees);
    } else {
        echo json_encode([]);
    }
?>