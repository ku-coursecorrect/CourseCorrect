/**
* @brief This is a helper function used to reduce the repetitiveness of this code as creating elements is done in several places
* @param type {string} The type of the DOM element to create
* @param parent {element or string} An existing DOM element or its ID to append the new element to (optional)
* @param text {string} The contents of the element (optional)
* @param value {string} The value of the element (optional)
**/
function makeElement(type, parent, text, value) {
	let el = document.createElement(type);
	if (value) el.value = value;
	if (text) el.appendChild(document.createTextNode(text));
	if (parent) {
		if (typeof parent === "string") parent = document.getElementById(parent);
		parent.appendChild(el);
	}
	return el;
}