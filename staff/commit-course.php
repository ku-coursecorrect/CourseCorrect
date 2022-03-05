<?php
	require_once "../common.php";
	require_once "course-common.php";
    require_staff();

    // TODO: Handle errors (such as a requisite course id not existing or course code already existing)

    if (array_key_exists('course_id', $_POST)) { // Only do anything if this page was accessed via the edit-course form
        $values = [
            'course_code' => $_POST['course_code'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'hours' => intval($_POST['hours']),
            'f_spring' => $_POST['f_spring'] === 'true' ? 1 : 0,
            'f_summer' => $_POST['f_summer'] === 'true' ? 1 : 0,
            'f_fall' => $_POST['f_fall'] === 'true' ? 1 : 0
        ];
        $course_id = $_POST['course_id'];
        if ($course_id === '') {
            // Create a new course
            $keys_str = implode(",", array_keys($values));
            $course_id = $db->query('INSERT into course ('.$keys_str.') VALUES ('.implode(',', array_fill(0, count($values), '?')).');', array_values($values));
        } else {
            // Edit an existing course
            $val_str = "";
            $val_ind = 0;
            foreach (array_keys($values) as $val) {
                $val_str .= $val . '=?';
                if ($val_ind !== count($values)-1) {
                    $val_str .= ',';
                }
                $val_ind++;
            }
            $db->query('UPDATE course set ' . $val_str . ' where course_id=?', array_merge(array_values($values), [intval($course_id)]));
            // Remove old reqs for this course
            $db->query('DELETE from requisite where course_id=?', [intval($course_id)]);
        }

        // Add all course requisites to requisite table
        $reqs = json_decode($_POST['requisites'], true);
        foreach ($reqs as $req) {
            $dependency_id = intval($db->query("SELECT course_id FROM course where course_code=?;", [$req["course_code"]])[0]["course_id"]);
            $vals = [intval($course_id), $dependency_id, $req["co_req"] ? 1 : 0, $req["start_semester"], $req["end_semester"]];
            $db->query("INSERT into requisite (course_id, dependent_id, co_req, start_semester, end_semester) VALUES(?, ?, ?, ?, ?);", $vals);
        }
    }
    // Redirect back to the courses page
    header("Location: edit-courses.php");
    exit;
?>