IN THE CONTENT DATATYPE VIEW EXAM TPL
{* THIS IS DISPLAYED ON THE BACKEND IN THE VIEW TAB *}

{def $exam=fetch( 'examen', 'examen', hash( 'id', $node.object.id ) )}
{$node.object.id|exam("node object id")}
{foreach $exam.options as $options}

{/foreach}

{foreach $attribute.content.structure as $element}
	{switch match=$element.type}
		{case match="text"}
			{exam_view_gui element=$element}
		{/case}
		{case match="question"}
			{exam_view_gui element=$element}
		{/case}
	{/switch}
{/foreach}
