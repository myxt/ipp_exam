{def $pagebreak=false()
	$condition=false()}
<div class="content-view-full">
    <div class="class-exam">

    <div class="attribute-title">
    {attribute_view_gui attribute=$node.object.data_map.title}
    </div>
    <div class="attribute-intro">
    {attribute_view_gui attribute=$node.object.data_map.intro}
    </div>
{*if exam has no page breaks then it can all be handled here*}
	{foreach $node.object.data_map.exam_attributes.content.structure as $element}
		{if eq($element.type,"group")}
			{if ne(count($element.children),0)}
				{foreach $element.children as $child}
					{if eq($child.type,"pagebreak")}
						{foreach $child.answers as $answer}
							{if ne($answer.option_value,0)}
								{set $condition=true()}
								{break}
							{/if}
						{/foreach}
					{/if}
					{if eq($child.type,"pagebreak")}
						{set $pagebreak=true()}
						{break}
					{/if}
				{/foreach}
			{/if}
			{if eq($pagebreak,true())}
				{break}
			{/if}
			{if eq($condition,true())}
				{break}
			{/if}
		{/if}
		{if eq($element.type,"question")}
			{foreach $element.answers as $answer}
				{if ne($answer.option_value,0)}
					{set $condition=true()}
					{break}
				{/if}
			{/foreach}
		{/if}
		{if eq($element.type,"pagebreak")}
			{set $pagebreak=true()}
		{/if}
		{if eq($pagebreak,true())}
			{break}
		{/if}
		{if eq($condition,true())}
			{break}
		{/if}
	{/foreach}

{*There are two modes at this point - simple and complicated - if an exam has no pagebreaks, has no conditions and is less than 10 questions then it should go to simple - otherwise it should go to complicated.  The default should be one element per page from that point on, but, if there are no follow conditions and there are page breaks maybe we can do multiple questions per page*}

	{*if there are no pagebreaks and no conditions and there are less than 10 questiosn then we can do it easy*}
	{if and(eq($pagebreak,false()),eq($condition,false()),lt($node.object.data_map.exam_attributes.content.structure|count,10))}
		{*This is the simple mode, for short quizes/surveys that have not conditions and no pagebreaks - should go to exam and drop straight to the results section*}
			<form name="simple exam" method="post" action={'examen/exam/'|ezurl}>
			<input type="hidden" name="exam_id" value="{$node.object.id}">
			<input type="hidden" name="exam_version" value="{$node.contentobject_version}">
			<input type="hidden" name="exam_language" value="{$node.object.current_language}">
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
		<form name="advanced exam" method="post" action={'examen/exam/'|ezurl}>
			<input type="hidden" name="exam_id" value="{$node.object.id}">
			<input type="hidden" name="exam_version" value="{$node.contentobject_version}">
			<input type="hidden" name="exam_language" value="{$node.object.current_language}">
			<input class="button" type="submit" name="SubmitButton" value="{'Start Exam'|i18n( 'design/exam' )}" title="{'Start Exam'|i18n( 'design/exam' )}" />
		</form>
	{/if}
</div>
