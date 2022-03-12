<?php
    // Requisite information can come from _GETs or from PHP via $req
    $req['course_code'] = $_GET['course_code'] ?? $req['course_code'] ?? ''; // Null coalesce (pick B if A is null)
    $req['co_req'] = $_GET['co_req'] ?? $req['co_req'] ?? 'false';
    $req['start_season'] = $_GET['start_season'] ?? $req['start_season'] ?? 'None';
    $req['end_season'] = $_GET['end_season'] ?? $req['end_season'] ?? 'None';
    $req['start_year'] = $_GET['start_year'] ?? $req['start_year'] ?? '';
    $req['end_year'] = $_GET['end_year'] ?? $req['end_year'] ?? '';
    $req['req_num'] = $_GET['req_num'] ?? $req['req_num'] ?? '';

    $placeholder['course_code'] = $req['course_code'] ?: 'EECS 168';
    $placeholder['start_year'] = $req['start_year'] ?: 'year'; // Coalesce (pick B if A is false)
    $placeholder['end_year'] = $req['end_year'] ?: 'year';
?>
<tr class='req'>
    <td>
        <div class="autoComplete_wrapper">
            <input autocomplete="off" style='width:300px;' type='text' id='reqCode-<?=$req['req_num']?>' class='form-control' maxlength="12" placeholder='<?=$placeholder['course_code']?>' value='<?=$req['course_code']?>' />
        </div>
    </td>
    <td><button class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='<?=$req['co_req']==='1' ? 'coreq' : 'prereq'?>' id='reqDrop'><?=$req['co_req']==='1' ? 'Corequisite' : 'Prerequisite'?></button>
        <div class='dropdown-menu'>
        <a class='dropdown-item' <?=$req['co_req']==='0' ? 'selected' : ''?> value='prereq' onclick='dropdownSelect(this);'>Prerequisite</a>
        <a class='dropdown-item' <?=$req['co_req']==='1' ? 'selected' : ''?> value='coreq' onclick='dropdownSelect(this);'>Corequisite</a>
        </div>
    </td>
    <td>
        <div class='input-group' data-toggle=tooltip data-placement=auto title='The first semester for which this requisite is in effect for this course'>
            <button id='req-sem' class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='<?=strtolower($req['start_season'])?>' id='startSem'><?=$req['start_season']?></button>
            <div class='dropdown-menu'>
                <a class='dropdown-item' <?=$req['start_season']=='None' ? 'selected' : ''?> value='none' onclick='dropdownSelect(this)'>None</a>
                <a class='dropdown-item' <?=$req['start_season']=='Spring' ? 'selected' : ''?> value='spring' onclick='dropdownSelect(this)'>Spring</a>
                <a class='dropdown-item' <?=$req['start_season']=='Summer' ? 'selected' : ''?> value='summer' onclick='dropdownSelect(this)'>Summer</a>
                <a class='dropdown-item' <?=$req['start_season']=='Fall' ? 'selected' : ''?> value='fall' onclick='dropdownSelect(this)'>Fall</a>
            </div>
            <input type='number' id='startYear' class='form-control' placeholder='<?=$placeholder['start_year']?>' value='<?=$req['start_year']?>' style='width: 50px; padding:0px; text-align:center; display:<?=$req['start_season']=='None' ? 'none' : ''?>;' maxlength='4'/>
        </div>
    </td>
    <td>
        <div class='input-group' data-toggle=tooltip data-placement=auto title='The final semester for which this requisite is in effect for this course'>
            <button id='req-sem' class='btn btn-outline-secondary dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' value='<?=strtolower($req['end_season'])?>' id='endSem'><?=$req['end_season']?></button>
            <div class='dropdown-menu'>
                <a class='dropdown-item' <?=$req['end_season']=='None' ? 'selected' : ''?> value='none' onclick='dropdownSelect(this)'>None</a>
                <a class='dropdown-item' <?=$req['end_season']=='Spring' ? 'selected' : ''?> value='spring' onclick='dropdownSelect(this)'>Spring</a>
                <a class='dropdown-item' <?=$req['end_season']=='Summer' ? 'selected' : ''?> value='summer' onclick='dropdownSelect(this)'>Summer</a>
                <a class='dropdown-item' <?=$req['end_season']=='Fall' ? 'selected' : ''?> value='fall' onclick='dropdownSelect(this)'>Fall</a>
            </div>
            <input type='number' id='endYear' class='form-control' placeholder='<?=$placeholder['end_year']?>' value='<?=$req['end_year']?>' style='width: 50px; padding:0px; text-align:center; display:<?=$req['end_season']=='None' ? 'none' : ''?>;' maxlength='4'/>
        </div>
    </td>
    <td>
        <i class='fas fa-trash ml-3' onclick='removeReq(this)'></i>
    </td>
</tr>
