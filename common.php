<?php
	require_once "../db.php";
	
	define("DATE_FORMAT", "M jS, Y"); // Mar 15th, 2020
	
	// Status codes bit flags
	abstract class PlanStatus {
		const Complete = 1;
		const Incomplete = 2;
		const Warning = 4;
		const Submitted = 8;
		const Approved = 16;
	}
	
	function planStatusToHTML($status) {
		$badges = "";
		if ($status & PlanStatus::Incomplete) $badges .= '<span class="badge badge-danger">Incomplete</span>';
		if ($status & PlanStatus::Warning) $badges .= '<span class="badge badge-warning">Warning</span>';
		if ($status & PlanStatus::Submitted) $badges .= '<span class="badge badge-info">Pending</span>';
		if ($status & PlanStatus::Approved) $badges .= '<span class="badge badge-success">Approved</span>';
		return $badges;
	}
	
	// Start the session to keep track of who's logged in
	session_start();
?>