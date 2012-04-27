function removeLine(childElement){
	if (document.getElementById(childElement)) {
		var child = document.getElementById(childElement);
		var parent = child.parentNode;
		
		parent.removeChild(child);
	}
	return false;
}

function addInfoLink(parentElement, lineId) {
	var parent = document.getElementById(parentElement);
	var newLine = document.createElement('tr');
	newLine.setAttribute('id', 'infoLink' + lineId);
	
	var newNameTD = document.createElement('td');
	
	var newNameInput = document.createElement('input');
	newNameInput.setAttribute('type', 'text');
	newNameInput.setAttribute('class', 'regular-text');
	newNameInput.setAttribute('name', 'infoLinksName[' + lineId + ']');
	newNameInput.setAttribute('style', 'width: 293px;');
	
	newNameTD.appendChild(newNameInput);
	newLine.appendChild(newNameTD);
	
	var newFieldTD = document.createElement('td');
	
	var newFieldInput = document.createElement('input');
	newFieldInput.setAttribute('type', 'text');
	newFieldInput.setAttribute('class', 'regular-text');
	newFieldInput.setAttribute('name', 'infoLinksField[' + lineId + ']');
	newFieldInput.setAttribute('style', 'width: 293px;');
	
	newFieldTD.appendChild(newFieldInput);
	newLine.appendChild(newFieldTD);
	
	var newRemoveButtonTD = document.createElement('td');
	var newRemoveButtonLink = document.createElement('a');
	newRemoveButtonLink.setAttribute('class', 'add-new-h2');
	newRemoveButtonLink.setAttribute('onclick', 'removeLine(\'infoLink' + lineId + '\');');
	
	var buttonName = document.createTextNode(objectI18n.removeButton);
	newRemoveButtonLink.appendChild(buttonName);
	
	newRemoveButtonTD.appendChild(newRemoveButtonLink);
	newLine.appendChild(newRemoveButtonTD);
	
	parent.appendChild(newLine);
	
	document.getElementById('addInfoLink').setAttribute('onClick', 'addInfoLink(\'infoLinks\', ' + (lineId+1) + ');');
}
