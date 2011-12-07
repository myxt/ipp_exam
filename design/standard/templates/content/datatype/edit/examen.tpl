{*This is getting the structure to loop through the top level items since children will have to be handled on a lower level have to get the elements for questions so that the condition list can be populated.  Group needs it to pass to a child question. *}

{def $elements=fetch('examen', 'elements', hash(	'id', $attribute.contentobject_id,
										'version', $attribute.version,
										'language_code', $attribute.language_code )
				)
	$structure=fetch('examen', 'structure', hash( 	'id', $attribute.contentobject_id,
											'version', $attribute.version,
											'language_code', $attribute.language_code )
				)
}
{*
$("#id").scrollTop($("#id").scrollTop() + 100);
jQuery(function( $ )//called on document.ready
{
    var position = $(document).height() - $(window).height() - $(window).scrollTop();
	alert( "y" + position + "y" );
	window.scrollTo( 0, position );
});
function function1(){
    var position = $(document).height() - $(window).height() - $(window).scrollTop();
alert( "x" + position + "x" );
    window.scrollTo( 0, position );
}
*}
{literal}
<script language="JavaScript">
function goToByScroll(id){
     	$('html,body').animate({scrollTop: $("#"+id).offset().top},'slow');
}
goToByScroll("exam-edit");
</script>
{/literal}
<input type="button" value="Scroll to Bottom" onClick="function1();">
<input type="button" value="Scroll to top" onClick="$(window).scrollTop();">

<div id="exam-edit">
	<div class="buttons">
		<input class="button" id="newGroup" name="CustomActionButton[newGroup]" value="{'New Group'|i18n('design/exam')}" type="Submit">
		<input class="button" id="newQuestion" name="CustomActionButton[newQuestion]" value="{'New Question'|i18n('design/exam')}" type="Submit">
		<input class="button" id="newText" name="CustomActionButton[newText]" value="{'New Text'|i18n('design/exam')}" type="Submit">
		<input class="button" id="newBreak" name="CustomActionButton[newBreak]" value="{'New Pagebreak'|i18n('design/exam')}" type="Submit">
		<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities]" value="{'Update Priorities'|i18n('design/exam')}" type="Submit">
	</div>

	{foreach $structure as $element}
		{if or(eq($element.type,"question"),eq($element.type,"group"))} {*need elements for condition choices*}
			{exam_edit_gui element=$element elements=$elements}
		{else}
			{exam_edit_gui element=$element}
		{/if}
	{/foreach}
	{if ne($elements|count,0)}
		<div class="buttons">
			<input class="button" id="newGroup" name="CustomActionButton[newGroup]" value="{'New Group'|i18n('design/exam')}" type="Submit">
			<input class="button" id="newQuestion" name="CustomActionButton[newQuestion]" value="{'New Question'|i18n('design/exam')}" type="Submit">
			<input class="button" id="newText" name="CustomActionButton[newText]" value="{'New Text'|i18n('design/exam')}" type="Submit">
			<input class="button" id="newBreak" name="CustomActionButton[newBreak]" value="{'New Pagebreak'|i18n('design/exam')}" type="Submit">
			<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities]" value="{'Update Priorities'|i18n('design/exam')}" type="Submit">
		</div>
	{/if}
</div>