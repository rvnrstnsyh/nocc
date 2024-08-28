<?php

/**
 * Integration of ckeditor5
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

print('<script src="ckeditor5/ckeditor.js"></script>');
print('<script type="text/javascript">');
?>
ClassicEditor
.create(document.querySelector('#mail_body'),{
initialData: '<?php printf("%s", $ckeditor5_mb); ?>',
toolbar: { items: [
"heading",
"|",
"fontBackgroundColor",
"fontColor",
"fontSize",
"fontFamily",
"highlight",
"removeHighlight",
"|",
"bold",
"italic",
"underline",
"strikethrough",
"subscript",
"superscript",
"blockQuote",
"code",
"codeBlock",
"|",
"horizontalLine",
"pageBreak",
"|",
"removeFormat",
"|",
"imageTextAlternative",
"imageStyle:full",
"imageStyle:side",
"imageUpload",
"link",
"mediaEmbed",
"|",
"indent",
"outdent",
"numberedList",
"bulletedList",
"todoList",
"|",
"insertTable",
"tableColumn",
"tableRow",
"mergeTableCells",
"tableCellProperties",
"tableProperties",
"|",
"specialCharacters",
"MathType",
"ChemType",
"|",
"undo",
"redo",
],
shouldNotGroupWhenFull: true,
}})
.then( editor => {
//console.log( editor );
//console.log(Array.from( editor.ui.componentFactory.names()));
})
.catch( error => {
console.error( error );
});

<?php
print('</script>');
