<?php
    require_once "../../common.php";
    require_staff();
    header('Content-type: application/json');
    echo json_encode($db->query("SELECT * FROM course;"));
?>