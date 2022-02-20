<?php
	require_once "../common.php";
	require_once "course-common.php";
    require_staff();

    if (array_key_exists('course_id', $_POST)) { // Only do anything if this page was accessed via the edit-course form
        $values = [
            'course_code' => $_POST['course_code'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'min_hours' => intval($_POST['min_hours']),
            'max_hours' => $_POST['variable'] === 'true' ? intval($_POST['max_hours']) : intval($_POST['min_hours']),
            'f_spring' => $_POST['f_spring'] === 'true' ? 1 : 0,
            'f_summer' => $_POST['f_summer'] === 'true' ? 1 : 0,
            'f_fall' => $_POST['f_fall'] === 'true' ? 1 : 0
        ];
        if ($_POST['course_id'] === '') {
            // Create a new course
            // TODO: Handle creation of new courses
            $db->query();
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
            $db->query('update course set ' . $val_str . ' where course_id=?', array_merge(array_values($values), [intval($_POST['course_id'])]));
            // Remove old reqs for this course
            $db->query('delete from requisite where course_id=?', intval($_POST['course_id']));
        }
        // TODO: Add requisites
    }

    // Redirect back to the courses page
    echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.'edit-courses.php'.'">';
    exit;
?>