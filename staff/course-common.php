<?php
require_once "../common.php";
require_staff();

// Map each course to it's requisites
function getReqs($reqs_array=NULL) {
    global $db;
        
    if ($reqs_array == NULL) {
        $reqs_array = $db->query("SELECT * FROM requisite;");
    }

    $requisites = [];
    foreach($reqs_array as $req) {
        if (!array_key_exists($req["course_id"], $reqs_array)) {
            $requisites[$req["course_id"]] = [];
        }
        array_push($requisites[$req["course_id"]], $req);
    }

    return $requisites;
}

?>