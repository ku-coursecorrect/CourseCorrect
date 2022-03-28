<?php
require_once "../../common.php";
require_staff();

// Map each course to it's requisites
function getReqs($reqs_array=NULL) {
    global $db;
        
    if ($reqs_array == NULL) {
        $reqs_array = $db->query("SELECT * FROM requisite;");
    }

    $requisites = [];
    foreach($reqs_array as $req) {
        if (!array_key_exists($req["course_id"], $requisites)) {
            $requisites[$req["course_id"]] = [];
        }
        array_push($requisites[$req["course_id"]], $req);
    }

    return $requisites;
}

const ULE_OPTIONS = [
    0 => "Unaffected",
    1 => "Requirement for ULE",
    2 => "Last Semester Exception",
    3 => "Requires ULE Completion"
];
const ULE_HINTS = [
    0 => "Course that you can take whenever you want and have nothing to do with ULE",
    1 => "Course that you are required to take to earn ULE",
    2 => "Course that can be taken in the same semester the last required ULE courses are being completed",
    3 => "Course that requires ULE to be completed (or a waiver) to take"
];
?>