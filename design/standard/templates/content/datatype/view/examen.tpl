IN THE CONTENT DATATYPE VIEW EXAM TPL

{def $exam=fetch( 'examen', 'examen', hash( 'id', $node.object.id ) )}

{foreach $exam.options as $options}

{/foreach}

{foreach $exam.structure as $index => $element}
	{switch match=$element.type}
		{case="group"}{/case}{*for view we don't care edit we do*}
		{case="pagebreak"}{break}{/case}{*not sure what we'll do here*}
		{case="text"}{$element['content']}{/case}
		{case="question"}
			{$element['content']}
			{foreach $element.content.answers as $answer}
				{$element['content']}
			{/foreach}
		{/case}
	{/switch}
{/foreach}