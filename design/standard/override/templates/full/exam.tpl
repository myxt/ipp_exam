{*can't be cached otherwise random order isn't random*}
{set-block scope=root variable=cache_ttl}0{/set-block}
{def $status=cond(ezhttp_hasvariable(concat('status[',$node.object.id,']'), 'session'),ezhttp(concat('status[',$node.object.id,']'), 'session' ),false())}
{*if it has a status then we are getting here without clearing the session variables in result.php - which means someone quit halfway through - have to handle it*}

{def $pagebreak=false()
	$condition=false()
	$structure=$node.object.data_map.exam_attributes.content.structure
	$survey=eq($node.object.data_map.pass_threshold.data_int,0)
}
{if $node.data_map.facebook_text.has_content}
	{if $node.data_map.facebook_title.has_content}
		<meta name="title" content="{$node.data_map.facebook_title.content|strip_tags}">
	{else}
		<meta name="title" content="{$node.name}">
	{/if}
		<meta name="description" content="{$node.data_map.facebook_text.content|strip_tags}" />
	{if $node.data_map.facebook_image.content.is_valid}
		<link rel="image_src" href={$node.data_map.facebook_image.content.['large'].url|ezurl} />
	{else}
		<link rel="image_src" href={"facebook_image_default.png"|ezimage} />
	{/if}
{/if}
<div class="content-view-full">
    <div class="class-exam">

    <div class="attribute-title">
    {attribute_view_gui attribute=$node.object.data_map.title}
    </div>
    <div class="attribute-intro">
    {attribute_view_gui attribute=$node.object.data_map.intro}
    </div>
	{if lt(currentdate(),$node.object.data_map.start_date.data_int)}
		{if $survey}
			{"This survey is not available yet."|i18n( 'design/exam' )}
		{else}
			{"This exam is not available yet."|i18n( 'design/exam' )}
		{/if}
	{elseif and(gt(currentdate(),$node.object.data_map.end_date.data_int),gt($node.object.data_map.end_date.data_int,0))}
		{if $survey}
			{"This survey is no longer available."|i18n( 'design/exam' )}
		{else}
			{"This exam is no longer available."|i18n( 'design/exam' )}
		{/if}
	{elseif and(eq($node.object.data_map.is_retest.data_int,1),eq($status,false()))}
	{*if we land on an element that is marked as a retest only, we better have a status *}
		{if $survey}
			{"This survey is not available yet."|i18n( 'design/exam' )}
		{else}
			{"This exam is not available yet."|i18n( 'design/exam' )}
		{/if}
	{else}
		{*should check for status session variable and just go to results if it's set to done here*}
		{*Have to see if it's a survey or exam first*}
		{*Since a session can now be re-used, this is no longer possible*}
		{*if eq( $status, "DONE" ) }
			<div class="exam-message">
				{if $survey}
					{"You have already taken this survey today for the maximum allowed times."|i18n( 'design/exam' )}
				{else}
					{"You have already taken this exam today for the maximum allowed times."|i18n( 'design/exam' )}
				{/if}
				<form name="advanced exam" method="post" action={'examen/result/'|ezurl}>
					<input type="hidden" name="exam_id" value="{$node.object.id}">
					<input type="hidden" name="exam_version" value="{$node.contentobject_version}">
					<input type="hidden" name="exam_language" value="{$node.object.current_language}">
					<input class="button" type="submit" name="SubmitButton" value="{'See Results'|i18n( 'design/exam' )}" title="{'See Results'|i18n( 'design/exam' )}" />
				</form>
			</div>
		{else*}
			{*if exam has no page breaks or conditions then it can all be handled here*}
			{foreach $structure as $element}
				{if eq($element.type,"group")}
					{if ne(count($element.children),0)}
						{foreach $element.children as $child}
							{if eq($child.type,"question")}
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

		{*There are two modes at this point - simple and complicated - if an exam has no pagebreaks, has no conditions and is less than 10 questions then it should go to simple - otherwise it should go to complicated.  The default should be one element per page from that point on, but, if there are no follow conditions and there are page breaks we can do multiple questions per page*}

			{*if there are no pagebreaks and no conditions and there are less than 10 questions then we can do it easy*}
			{if and(eq($pagebreak,false()),eq($condition,false()),lt($structure|count,11))}
				{*This is the simple mode, for short quizes/surveys that have not conditions and no pagebreaks - should go to exam and drop straight to the results section*}
					<form name="simple exam" method="post" action={'examen/exam/'|ezurl}>
					<input type="hidden" name="mode" value="simple">
					<input type="hidden" name="exam_id" value="{$node.object.id}">
					<input type="hidden" name="exam_version" value="{$node.contentobject_version}">
					<input type="hidden" name="exam_language" value="{$node.object.current_language}">
					<input type="hidden" name="exam_status" value="{$status}">
					{if $node.object.data_map.random}
						{set $structure = $structure}
					{/if}
					{foreach $structure as $element}
						{if eq($element.type,"group")}
							<div class="group text">
								{$element.content}
							</div>
							{foreach $element.randomChildren as $child}
								{if or(eq($child.type,"question"),eq($child.type,"text"))}
									{exam_view_gui element=$child simple=true()}
								{/if}
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
					<input type="hidden" name="exam_status" value="{$status}">
					<button class="start-examen" type="submit" name="SubmitButton" value="{'Start Exam'|i18n( 'design/exam' )}" data-toggle="button" />{' '|i18n( 'design/exam' )}</button>
				</form>
			{/if}
		{*/if*} {*hash != done*}
	{/if} {*within timestamps*}
	</div>
</div>
