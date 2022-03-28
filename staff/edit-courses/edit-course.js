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
function updateReqAutoComplete(req_num, course_code) {
    let req_id = "reqCode-" + req_num;
    let autoCompleteJS = new autoComplete({
        selector: "#" + req_id, 
        placeHolder: "EECS 101",
        data: {
                src: courses.filter(course => course["course_code"] !== course_code),
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
    document.getElementById(req_id).addEventListener('change', function() {
        document.getElementById(req_id).style.border = "";
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
            }
        });
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
            drop.nextElementSibling.nextElementSibling.style.display = drop.value == 'none' ? 'none' : '';
        }

    }, 1, selection);
}

function addReq() {
    let course_code = document.getElementById("course_code").value;
    let table = document.getElementById("reqs-table");
    table.insertRow();

    let current_req = nextReq;
    fetch("requisite.php?req_num="+current_req).then(
        response => response.text()
    ).then(function(text) {
        table.rows[table.rows.length -1].outerHTML = text;
        updateReqAutoComplete(current_req, course_code);
    });
    nextReq++;
}

function removeReq(btn) {
    let row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
}

function populateModal(btn) {
    nextReq = 0;

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
                updateReqAutoComplete(i, course_code);
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
    let course_code = document.getElementById("course_code").value;

    for (let req of reqs) {
        reqs_to_post.push({});
        let req_ind = reqs_to_post.length-1;
        let req_course_code_inp = req.children[0].children[0].children[0].children[0];
        let req_course_code = req_course_code_inp.value;

        if (!course_codes.includes(req_course_code) || req_course_code === course_code ) {
            req_course_code_inp.style.border = "solid red";
            return false;
        }

        reqs_to_post[req_ind]["course_code"] = req_course_code;
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
    fetch("get-dependents.php?course_code=" + course_code).then(json => json.json()).then(
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
    let course_code = btn.parentElement.parentElement.children[0].children[0].children[0].innerText;
    fetch("delete-course.php?course_code="+course_code).then(response => response.text()).then(text => location.reload());
}