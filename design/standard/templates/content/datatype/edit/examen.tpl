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
{literal}
<script language="JavaScript">
jQuery(function( $ )//called on document.ready overriden by code in design/admin2/templates/content/edit.tpl
{
    var docScrollTop = 0, el = $('#exam-edit');

    if ( document.body.scrollTop !== undefined )
    	docScrollTop = document.body.scrollTop;// DOM compliant
    else if ( document.documentElement.scrollTop  !== undefined )
    	docScrollTop = document.documentElement.scrollTop;// IE6 standards mode;

    // Do not set focus if user has scrolled
    if ( docScrollTop < 10 )
    {
    	window.scrollTo(0, Math.max( el.offset().top - 180, 0 ));
        el.focus();
    }
});
function goTop(){
    window.scrollTo( 0, 0 );
}
function goBottom(){
	window.scrollTo(0, document.body.scrollHeight );
}
function goExamEdit(){
	el = $('#exam-edit');
	window.scrollTo(0, Math.max( el.offset().top - 180, 0 ));
	el.focus();
}
</script>
{/literal}
<input class="button" type="button" value="{'Scroll to Bottom'|i18n('design/exam')}" onClick="goBottom();">
<input class="button" type="button" value="{'Scroll to Top'|i18n('design/exam')}" onClick="goTop();">

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
{if ne($elements|count,0)}
	<input class="button" type="button" value="{'Scroll to Exam Top'|i18n('design/exam')}" onClick="goExamEdit();">
	<input class="button" type="button" value="{'Scroll to Top'|i18n('design/exam')}" onClick="goTop();">
{/if}