// JavaScript Document

function iFrameOn(){
	richTextField.document.designMode = "On";
	richTextField.document.body.style.fontFamily = "Courier New";
	richTextField.document.body.style.fontSize = "10pt";
	richTextField.document.body.style.cursor = "text";
}
function iBold(){
	richTextField.document.execCommand('bold', false, null);	
	richTextField.focus();
}
function iUnderline(){
	richTextField.document.execCommand('underline', false, null);
	richTextField.focus();
}
function inimg(){
    var img = prompt ("Enter the image link", "");
    alert(img);
    if (img != null) {
        richTextField.document.execCommand('insertImage', false, img);
    }
}
function iItalic(){
	richTextField.document.execCommand('italic', false, null);
	richTextField.focus();
}
function iFont(fontname){
	richTextField.document.execCommand('fontname', false, fontname);
	richTextField.focus();
}
function iFontSize(){
	var size = prompt('Enter a size between 1-7', '');
	if (size >= 1 && size <= 7){
		richTextField.document.execCommand('FontSize', false, size);
	}
	richTextField.focus();
}
function iForeColor(){
	var color = prompt('Define a basic color or a hexadecimal color code', '');
	richTextField.document.execCommand('ForeColor', false, color);
	richTextField.focus();
}
function iHorizontalRule(){
	richTextField.document.execCommand('inserthorizontalrule', false, null);
	richTextField.focus();
}