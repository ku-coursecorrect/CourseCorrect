let timeout = null;
let HIGHLIGHT = ["<span style=\"background-color:yellow;\">", "</span>"]; // ' are converted to " in HTML

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
            let posText = column.innerText.toUpperCase().indexOf(filter);
            let posHTML = column.innerHTML.toUpperCase().indexOf(filter);
            if (posText != -1 && posHTML != -1) {
                highlightWord(column, posHTML, posHTML+filter.length);
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