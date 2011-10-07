{def $pagebreak=false()}
<div class="content-view-full">
    <div class="class-exam">

    <div class="attribute-title">
    {attribute_view_gui attribute=$node.object.data_map.title}
    </div>
    <div class="attribute-intro">
    {attribute_view_gui attribute=$node.object.data_map.intro}
    </div>

	{*should maybe initialize the result here and .... what*}
	{*if exam has no page breaks then it can all be handled here*}
	{foreach $node.object.data_map.exam_attributes.content.structure as $element}
		{if eq($element.type,"group")}
			{if ne(count($element.children),0)}
				{foreach $element.children as $child}
					{set $pagebreak=true()}
					{break}
				{/foreach}
			{/if}
		{/if}
		{if eq($element.type,"pagebreak")}
			{set $pagebreak=true()}
		{/if}
		{if eq($pagebreak,true())}
			{break}
		{/if}
	{/foreach}
	{*if eq($pagebreak,false())*}
	{if eq($pagebreak,false())}{* testing *}
		{*wait a sec - this can only work if there are no conditions either*}
		{*This is the simple mode, for short quizes/surveys that have not conditions and no pagebreaks - should go to exam to save and redirect to result*}
			{* <form name="simple exam" method="post" action={concat('examen/exam/',$node.object.id)|ezurl}> *}
			<form name="simple exam" method="post" action={'examen/exam/'|ezurl}>
			{* <form name="simple exam" method="post" action="http://docs/post.php"> *}
			<input type="hidden" name="exam_id" value="{$node.object.id}">
			<input type="hidden" name="exam_version" value="{$node.object.id}">
			<input type="hidden" name="exam_language" value="{$node.object.id}">
			{foreach $node.object.data_map.exam_attributes.content.structure as $element}
				{if eq($element.type,"group")}
					<div class="group text">
						{$element.content}
					</div>
					{foreach $element.children as $child}
						{exam_view_gui element=$child simple=true()}
					{/foreach}
				{/if}
				{if or(eq($element.type,"question"),eq($element.type,"text"))}
					{exam_view_gui element=$element simple=true()}
				{/if}
			{/foreach}
			<input class="button" type="submit" name="SubmitButton" value="{'Submit'|i18n( 'design/admin/node/view/full' )}" title="{'Submit'|i18n( 'design/admin/node/view/full' )}" />
		</form>
	{else} {*complicated mode*}
		<div class="attribute-exam_attributes">
			{*attribute_view_gui attribute=$node.object.data_map.exam_attributes*}
		</div>
	{/if}
</div>
