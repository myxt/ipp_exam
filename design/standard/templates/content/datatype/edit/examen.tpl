IN THE CONTENT EDIT EXAMEN TPL<br>
<input class="button" id="newGroup" name="CustomActionButton[newGroup]" value="New Group" type="Submit">
<input class="button" id="newQuestion" name="CustomActionButton[newQuestion]" value="New Question" type="Submit">
<input class="button" id="newText" name="CustomActionButton[newText]" value="New Text" type="Submit">
<input class="button" id="newBreak" name="CustomActionButton[newBreak]" value="New Pagebreak" type="Submit">
<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities]" value="Update Priorities" type="Submit">

{foreach $attribute.content.structure as $element}
	{exam_edit_gui element=$element}
{/foreach}