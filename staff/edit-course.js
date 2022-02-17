let MINLEN = 40;
let DELTA = 20;
let SUFFIX = "...";

let expand_timer = null;
let expand = true;

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
            document.getElementById("credits_max").style.display = ""; 
        } else {
            document.getElementById("credits_max_separator").style.display = "none"; 
            document.getElementById("credits_max").style.display = "none"; 
        }
    }, 1, btn);
}

function dropdownSelect(selection) {
    timeout = setTimeout(function(selection) {
        let drop = selection.parentNode.previousElementSibling;
        let choiceValue = selection.attributes.value.nodeValue;
        let choiceText = selection.text;
        drop.value = choiceValue;
        drop.innerText = choiceText;
    }, 1, selection);
}

function addReq() {
    let table = document.getElementById("reqs-table");
    let row = table.insertRow();
    row.innerHTML = table.rows[1].innerHTML; // TODO: Actually add new rows instead of duping
}

function removeReq(row) {
    // TODO
}