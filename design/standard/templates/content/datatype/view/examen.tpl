{* THIS IS DISPLAYED ON THE BACKEND IN THE VIEW TAB AND IF YOU ADD A TRANSLATION*}

{def $structure=fetch('examen', 'structure', hash( 	'id', $attribute.contentobject_id,
											'version', $attribute.version,
											'language_code', $attribute.language_code )
				)
}
{foreach $exam.options as $options}

{/foreach}
{foreach $structure as $element}
	{if or(eq($element.type,"question"),eq($element.type,"group"))} {*need elements for condition choices*}
		{exam_view_gui element=$element context="edit"}
	{else}
		{exam_view_gui element=$element context="edit"} 
	{/if}
{/foreach}