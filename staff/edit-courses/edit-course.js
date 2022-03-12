let MINLEN = 20;
let DELTA = 20;
let SUFFIX = "...";

let expand_timer = null;
let expand = true;

let courses = {};
let nextReq = 0;
fetch("get-courses.php").then((source) => source.json()).then((data) => courses = data );

// Add autocomplete functionality to a requisite input box
function updateReqAutoComplete(req_num) {
    let req_id = "reqCode-" + req_num;
    let autoCompleteJS = new autoComplete({
        selector: "#" + req_id, 
        placeHolder: "EECS 101",
        data: {
                src: courses,
                keys: ["course_code", "title"]
            },
        resultsList: {
            element: (list, data) => {
                const info = document.createElement("p");
                if (data.results.length > 0) {
                    info.innerHTML = `Displaying <strong>${data.results.length}</strong> out of <strong>${data.matches.length}</strong> results`;
                } else {
                    info.innerHTML = `Found <strong>${data.matches.length}</strong> matching results for <strong>"${data.query}"</strong>`;
                }
                list.prepend(info);
            },
            noResults: true,
            maxResults: 15,
            tabSelect: true
        },
        resultItem: {
            element: (item, data) => {
                item.style = "display: flex; justify-content: space-between;";
                item.innerHTML = `
                <span style="white-space: nowrap; overflow: hidden;">
                ${data.value.course_code}
                </span>
                <span style="white-space: nowrap; width:50%; overflow: hidden; text-overflow: ellipsis; align-items: left; font-size: 13px; font-weight: 100; text-transform: uppercase;">
                ${data.value.title}
                </span>`;
            }
        },
        events: {
            input: {
                focus: () => {
                    if (autoCompleteJS.input.value.length) autoCompleteJS.start();
                }
            }
        }
    });
    autoCompleteJS.input.addEventListener("selection", function (event) {
        const feedback = event.detail;
        autoCompleteJS.input.blur();
        const selection = feedback.selection.value['course_code'];
        document.querySelector("#" + req_id).innerHTML = selection;
        autoCompleteJS.input.value = selection;
    });
}

function expandText(e, full_text, click, delta=DELTA, expand_=null) {
    if (expand_timer) {  
        clearTimeout(expand_timer);
    }
    if (e.target.innerText.endsWith(SUFFIX))
    {
        e.target.innerText = e.target.innerText.substring(0, e.target.innerText.length-3);
    }

    let current_len = e.target.innerText.length;
    let target_len = full_text.trim().length;

    if (click) {
        expand = current_len != target_len;
    }
    if (expand_ !== null) {
        expand = expand_;
    }

    // Get bigger
    if (expand && current_len < target_len) {
        e.target.style = "";
        let i = delta;
        while (e.target.innerText.length == current_len) { // Keep adding chars until the length changes since spaces get trimmed immediately
            e.target.innerText = full_text.substring(0, current_len + i);
            i += delta;
        }
        if (e.target.innerText.length == target_len) {
            return;
        }
    }

    // Get smaller
    else if (!expand && current_len > MINLEN) {
        e.target.style = "font-style:italic";
        e.target.innerText = full_text.substring(0, current_len - delta);
        if (e.target.innerText.length <= MINLEN) {
            e.target.innerText = full_text.substring(0, MINLEN) + SUFFIX;
            return;
        }
    }

    expand_timer = setTimeout(function() {
        expandText(e, full_text, false);
    }, 2);
}

let timeout = null;
let HIGHLIGHT = ["<span style=\"background-color:yellow;\">", "</span>"]; // ' are converted to " in HTML

function getFullText(ele) {
    let text_html = ele.attributes.onclick.nodeValue;
    let start = text_html.indexOf("\"");
    let end = text_html.lastIndexOf("\"");
    return text_html.substring(start + 1, end);
}

function waitFilter() {
    if (timeout) {  
        clearTimeout(timeout);
    }
    timeout = setTimeout(function() {
        filterTable();
    }, 150);
}
function filterTable() {
    let input = document.getElementById("filterTableInput");
    let filter = input.value.toUpperCase();
    let table = document.getElementById("classTable");
    let rows = table.getElementsByTagName("tr");

    for (let row of rows)
    {
        if (row.cells[0].tagName == "TH") {
            continue;
        }
        for (let column of row.cells) {
            column.innerHTML = column.innerHTML.replace(HIGHLIGHT[0], ""); // Clean old highlights first
            column.innerHTML = column.innerHTML.replace(HIGHLIGHT[1], "");
        }
        if (filter === "") {
            row.style.display = "";
            continue;
        }
        let found = false;
        for (let column of row.cells) {
            let expand = true;
            let ele = column.querySelector('.expand');
            if (ele === null) {
                ele = column;
                expand = false;
            }
            let posText = ele.innerText.toUpperCase().indexOf(filter);
            let posHTML = ele.innerHTML.toUpperCase().indexOf(filter);
            if (posText != -1 && posHTML != -1) {
                if (expand) {
                    let full_text = getFullText(ele);
                    let e = {target: ele};
                    expandText(e, full_text, true, 1000000, true);
                    highlightWord(ele, posHTML, posHTML+filter.length);
                } else {
                    highlightWord(ele, posHTML, posHTML+filter.length);
                }
                found = true;
                break;
            }
        }
        if (!found) {
            row.style.display = "none";
        } else {
            row.style.display = "";
        }
    }
    function highlightWord(column, startPos, endPos) {
        column.innerHTML = column.innerHTML.slice(0, startPos) + HIGHLIGHT[0] + column.innerHTML.slice(startPos, endPos) + HIGHLIGHT[1] + column.innerHTML.slice(endPos);
    }
}

function toggleCredits(btn) {
    timeout = setTimeout(function(btn) {
        if (btn.ariaPressed === 'true') { // Apparently this isn't set immediately. Timeout works
            document.getElementById("credits_max_separator").style.display = ""; 
            document.getElementById("max_hours").style.display = ""; 
        } else {
            document.getElementById("credits_max_separator").style.display = "none"; 
            document.getElementById("max_hours").style.display = "none"; 
        }
        btn.value = btn.ariaPressed;
    }, 1, btn);
}

function dropdownSelect(selection) {
    timeout = setTimeout(function(selection) {
        let drop = selection.parentNode.previousElementSibling;
        let choiceValue = selection.attributes.value.nodeValue;
        let choiceText = selection.text;
        drop.value = choiceValue;
        drop.innerText = choiceText;

        // Hide or show year box if altering a requisite semester
        if (drop.id == "req-sem") {
            if (drop.value == 'none') {
                drop.nextElementSibling.nextElementSibling.style.display='none';
            } else {
                drop.nextElementSibling.nextElementSibling.style.display='';
            }
        }

    }, 1, selection);
}

function addReq() {
    let table = document.getElementById("reqs-table");
    table.insertRow();

    let current_req = nextReq;
    fetch("requisite.php?req_num="+current_req).then(
        response => response.text()
    ).then(function(text) {
        table.rows[table.rows.length -1].outerHTML = text;
        updateReqAutoComplete(current_req);
    });
    nextReq++;
}

function removeReq(btn) {
    let row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
    // TODO[reece]: Warn user of associated requisites?
}

function populateModal(btn) {
    let course_code = "";
    
    if (btn.innerText.trim() === "Add new course") {
        course_code = "New";
    } else {
        let course_row = btn.parentNode.parentNode;
        course_code = course_row.children[0].innerText;
    }

    fetch("edit-course.php?course_code="+course_code).then(
        response => response.text()
    ).then(
        function(text) {
            document.getElementById("edit-course").innerHTML = text;
            // Find last req in course to determine how many need updated with autocomplete
            let req_pos = text.lastIndexOf("reqCode");
            let req_end = text.indexOf("'", req_pos);
            let req_id = text.slice(req_pos + "reqCode-".length, req_end);
            for (let i = 0; i <= req_id; i++) {
                // Update all reqs with autocomplete functionality
                updateReqAutoComplete(i);
            }
    });
}

// Set a hidden input following a button to the state of the button
// So that the value is included in the form POST
function addPost(btn) {
    timeout = setTimeout(function(btn) {
        let input = btn.nextElementSibling;
        input.value = btn.ariaPressed;
    }, 3, btn); // wait 3 to ensure this occurs after the value is changed
}

function to_Semester(year, season) {
    season_nums = {
        "spring": 0,
        "summer": 1,
        "fall": 2
    };

    return year * 3 + season_nums[season];
}

// Grab values for reqs and place in the reqs input value for the form 
function updateReqsPost() {
    let post_input_ele = document.getElementById('reqs-post');
    let reqs_to_post = [];

    let reqs = document.getElementsByClassName('req');
    for (let req of reqs) {
        reqs_to_post.push({});
        let req_ind = reqs_to_post.length-1;
        reqs_to_post[req_ind]["course_code"] = req.children[0].children[0].children[0].children[0].value;
        reqs_to_post[req_ind]["co_req"] = req.children[1].children[0].value === 'co_req';
        
        let start_season = req.children[2].children[0].children[0].value;
        let start_year = req.children[2].children[0].children[2].value;
        let start_semester = start_season === "None" ? null : to_Semester(start_year, start_season);
        reqs_to_post[req_ind]["start_semester"] = start_semester;
        
        let end_season = req.children[3].children[0].children[0].value;
        let end_year = req.children[3].children[0].children[2].value;
        let end_semester = end_season === "None" ? null : to_Semester(end_year, end_season);
        reqs_to_post[req_ind]["end_semester"] = end_semester;
    }

    post_input_ele.value = JSON.stringify(reqs_to_post);
    return true;
}

function deleteModal(btn) {
    let course_code = btn.parentElement.parentElement.children[0].innerText;
    let subtitle = document.getElementById("delete-subtitle");
    subtitle.innerText = course_code;
}

function deleteCourse(btn) {
    let course_code = btn.parentElement.parentElement.children[0].children[0].children[0].innerText;
    fetch("delete-course.php?course_code="+course_code).then(response => response.text()).then(text => location.reload());
}