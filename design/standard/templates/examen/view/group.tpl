<div id="exam-view">
	<div id="element group {$element.id}">
		{$element.getXMLContent}
	<div class="children">
		{foreach $element.children as $child}
			{if eq($element.type,"question")} {*need elements for condition choices*}
				{exam_view_gui element=$child random="false"}
			{else}
				{exam_view_gui element=$child random="false"}
			{/if}
		{/foreach}
	</div>
	</div>
</div>
