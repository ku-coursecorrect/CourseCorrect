let courses = {};
let course_codes = {};
let nextReq = 0;
let rmjs = null;
let marks = [];

fetch("get-courses.php").then((source) => source.json()).then(function(data) {
    courses = data;
    course_codes = data.map((value) => value.course_code);
});

window.addEventListener('load', function() {
    rmjs = new Readmore('article', {
        "collapsedHeight": 50,
        "moreLink": '<a href="#">More</a>',
        "lessLink": '<a href="#">Close</a>',
    });
});

// Add autocomplete functionality to a requisite input box
function updateReqAutoComplete(req_num, course_id) {
    let req_code = "reqCode-" + req_num;
    let req_id = "reqId-" + req_num;
    let autoCompleteJS = new autoComplete({
        selector: "#" + req_code, 
        placeHolder: "EECS 101",
        data: {
                src: courses.filter(course => course["course_id"] != course_id),
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
                list.classList.add("list-group");
                list.style="overflow-y: auto; overflow-x: hidden; height: 150px; width: fit-content";
            },
            noResults: true,
            maxResults: 100000,
            tabSelect: true,
            destination: "#autoComplete-" + req_num
            
        },
        resultItem: {
            element: (item, data) => {
                item.innerHTML = `
                <span class="callout" data-toggle=tooltip data-placement=auto title='Course ID'>${data.value.course_id}</span>
                <span style="white-space: nowrap; width: 12em;">
                ${data.value.course_code}
                </span>
                <span style="display: inline-block; margin-left: 20px;" ></span>
                <span style="white-space: nowrap; font-size: 13px; font-weight: 100; text-transform: uppercase;">
                ${data.value.title}
                </span>`;
                item.classList.add("list-group-item")
            }
        },
        events: {
            input: {
                focus: () => {
                    document.getElementById("autoComplete-" + req_num).parentElement.parentElement.style.display="";
                    if (autoCompleteJS.input.value.length) autoCompleteJS.start();
                }
            }
        }
    });
    autoCompleteJS.input.addEventListener("selection", function (event) {
        const feedback = event.detail;
        autoCompleteJS.input.blur();
        const selection_id = feedback.selection.value['course_id'];
        const selection_code = feedback.selection.value['course_code'];
        document.querySelector("#" + req_id).value = selection_id;
        document.querySelector("#" + req_code).value = selection_code;
        autoCompleteJS.input.value = selection_code;
    });
    autoCompleteJS.input.addEventListener("blur", function (event) {
        document.getElementById("autoComplete-" + req_num).parentElement.parentElement.style.display="none";
        const id_inp = event.target.parentElement.parentElement.children[0].children[0];
        const selection_id = id_inp.value;
        const selection_code = event.target.value.toUpperCase();

        // When closed preventively...
        // Check if course code exists and update course_id
        if (course_codes.indexOf(selection_code) !== -1) {
            id_inp.value = "";
            match = null;
            for (course of courses) {
                if (course["course_code"] == selection_code) {
                    match = course;
                    // Keep searching in case there's another course with the same code that matches the current course_id
                    if (course["course_id"] == selection_id) {
                        break;
                    }
                }
            }
            if (match !== null) {
                id_inp.value = match["course_id"];
                id_inp.innerText = match["course_id"];
            }
        // If course_id is set, update course_code to match
        } else if (selection_id != "") {
            event.target.value = "";
            for (course of courses) {
                if (course["course_id"] == selection_id) {
                    event.target.value = course["course_code"];
                    event.target.innerText = course["course_code"];
                    break;
                }
            }
        // Otherwise set field to blank
        } else {
            id_inp.value = "";
            event.target.value = "";
            id_inp.innerText = "";
            event.target.innerText = "";
        }
    });
    document.getElementById(req_code).addEventListener('change', function() {
        document.getElementById(req_code).style.border = "";
    });
}


let timeout = null;

function waitFilter() {
    if (timeout) {  
        clearTimeout(timeout);
    }
    timeout = setTimeout(function() {
        filterTable();
    }, 300);
}

function isCollapsed(ele) {
    return ele.getBoundingClientRect().height <= ele.readmore.collapsedHeight;
}

function filterTable() {
    // Clear markings
    for (let mark of marks) {
        mark.unmark();
    }

    let input = document.getElementById("filterTableInput");
    let filter = input.value.toUpperCase();
    let table = document.getElementById("classTable");
    let rows = table.getElementsByTagName("tr");

    for (let row of rows)
    {
        // Don't filter header row
        if (row.cells[0].tagName == "TH") {
            continue;
        }
        if (filter === "") {
            row.style.display = "";
            continue;
        }

        var instance = new Mark(row);
        marks.push(instance);
        instance.mark(filter, {
            "each": function(ele) {
                if (ele.parentNode.className == "desc") {
                    // Readmore expand block should be expanded for highlight

                    // Query selector returns elements stripped of readmore data for some reason
                    // Iterate list of elements to find correct one instead
                    for (let expand_ele of rmjs.elements) {
                        if (expand_ele.id === ele.parentNode.id) {
                            if (isCollapsed(expand_ele)) {
                                rmjs.toggle(expand_ele);
                            }
                            break
                        }
                    }
                }
            },
            "done": function(num) {
                if (num > 0) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            },
            "separateWordSearch": false
        });
    }
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
            drop.nextElementSibling.nextElementSibling.style.display = drop.value == 'none' ? 'none' : '';
        }

    }, 1, selection);
}

function addReq() {
    let course_id = document.getElementById("course_id").value;
    let table = document.getElementById("reqs-table");
    table.insertRow();

    let current_req = nextReq;
    fetch("requisite.php?req_num="+current_req).then(
        response => response.text()
    ).then(function(text) {
        table.rows[table.rows.length -1].outerHTML = text;
        updateReqAutoComplete(current_req, course_id);
    });
    nextReq++;
}

function removeReq(btn) {
    let row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
}

function populateModal(btn) {
    nextReq = 0;

    let course_id = "";
    
    if (btn.innerText.trim() === "Add new course") {
        course_id = "New";
    } else {
        let course_row = btn.parentNode.parentNode;
        course_id = course_row.children[0].children[0].innerText;
    }

    $('[data-toggle="tooltip"]').tooltip({selector: '[title]'});

    fetch("edit-course.php?course_id="+course_id).then(
        response => response.text()
    ).then(
        function(text) {
            document.getElementById("edit-course").innerHTML = text;
            // Find last req in course to determine how many need updated with autocomplete
            let req_pos = text.lastIndexOf("reqCode");
            let req_id = null;
            if (req_pos === -1) {
                // No reqs exist yet on course
                req_id = -1;
            } else {
                let req_end = text.indexOf("'", req_pos);
                req_id = text.slice(req_pos + "reqCode-".length, req_end);
            }
            for (let i = 0; i <= req_id; i++) {
                // Update all reqs with autocomplete functionality
                updateReqAutoComplete(i, course_id);
            }
            nextReq = req_id + 1;
            // Handle submit button manually to prevent enter from pressing default button
            let form_submit_button = document.getElementById("form-button-submit");
            form_submit_button.addEventListener('click', function(){
                if (checkCourseForm()) {
                    document.getElementById("edit-course-form").submit();
                }
              });
            document.getElementById("course_code").addEventListener('change', function() {
                document.getElementById("course_code").style.border = "";
            });
    });
}

// Set a hidden input following a button to the state of the button
// So that the value is included in the form POST
function addPost(btn) {
    timeout = setTimeout(function(btn) {
        let input = btn.nextElementSibling;
        input.value = btn.classList.contains("active");
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

function updateULEPost() {
    let ule_post_inp = document.getElementById("f_ule");
    let ule_drop = document.getElementById("uleDrop");
    ule_post_inp.value = ule_drop.value;
}

// Validate the form
function checkCourseForm() {
    let course_code_inp = document.getElementById("course_code");
    let hours_inp = document.getElementById("hours");
    if (course_code_inp.value === '') {
        course_code_inp.style.border = "solid red";
        return false;
    }
    if (hours_inp.value === '') {
        hours_inp.style.border = "solid red";
        return false;
    }
    updateULEPost();
    return updateReqsPost();
}

// Grab values for reqs and place in the reqs input value for the form 
function updateReqsPost() {
    let post_input_ele = document.getElementById('reqs-post');
    let reqs_to_post = [];

    let reqs = document.getElementsByClassName('req');
    let course_id = document.getElementById("course_id").value;

    for (let req of reqs) {
        reqs_to_post.push({});
        let req_ind = reqs_to_post.length-1;
        let req_course_code_inp = req.children[0].children[0].children[0].children[1].children[0];
        let req_course_id = req.children[0].children[0].children[0].children[0].children[0].value;

        if (req_course_id === course_id || req_course_id === "") {
            req_course_code_inp.style.border = "solid red";
            return false;
        }

        reqs_to_post[req_ind]["course_id"] = req_course_id;
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
    let course_id = btn.parentElement.parentElement.children[0].children[0].innerText;
    let course_code = btn.parentElement.parentElement.children[0].children[1].innerText;
    let subtitle = document.getElementById("delete-subtitle");
    subtitle.innerHTML = course_code + "<span class='callout' style='margin-left: 5px' data-toggle=tooltip data-placement=auto title='Course ID'>" + course_id + "</span>";
    fetch("get-dependents.php?course_id=" + course_id).then(json => json.json()).then(
        function (dependents) {
            let dependents_text = document.getElementById("dependents");
            if (dependents.length > 0) {
                dependents_text.innerHTML = "<ul class='list-group'><li class='list-group-item list-group-item-danger'><b>" + course_code + " will be removed as a requisite from the following courses:</b></li>";
                dependents_text.innerHTML += "<li class='list-group-item'>" + dependents.join("</li><li class='list-group-item'>") + "</li>";
                dependents_text.innerHTML += "</ul>";
            } else {
                dependents_text.innerHTML = "";
            }
        }
    )
}

function deleteCourse(btn) {
    let course_id = btn.parentElement.parentElement.children[0].children[0].children[0].children[0].innerText;
    fetch("delete-course.php?course_id="+course_id).then(response => response.text()).then(text => location.reload());
}