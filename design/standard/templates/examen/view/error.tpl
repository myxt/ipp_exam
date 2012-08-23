<div id="exam-error">
	<label>{'ERROR'|i18n('design/exam')}</label>
{foreach $errors as $error}
	<div class="error-message">
	{switch match=$error}
		{case match="i_can_haz_no_cookie"}
			{"Cookies must be enabled to participate."|i18n('design/exam')}
		{/case}
		{case match="no_exam_id"}
			{"No exam id found."|i18n('design/exam')}
		{/case}
		{case match="no_exam_language"}
			{"No exam language found."|i18n('design/exam')}
		{/case}
		{case match="no_exam_version"}
			{"No exam version found."|i18n('design/exam')}
		{/case}
		{case match="no_hash"}
			{"No hash found."|i18n('design/exam')}
		{/case}
		{case match="no_object"}
			{"No such object."|i18n('design/exam')}
		{/case}
		{case match="object_not_exam"}
			{"Wrong object type.  Check your exam id."|i18n('design/exam')}
		{/case}
		{case match="threshold_exceeded"}
			{"You've tried to many times for today.  Try again on another day."|i18n('design/exam')}
		{/case}
		{case match="date_out_of_bounds"}
			{"This object is not available."|i18n('design/exam')}
		{/case}
		{case match="user_timed_out"}
			{"You took too long to answer the questions."|i18n('design/exam')} <a href='/'|ezurl()>{"Try again."|i18n('design/exam')}</a>
		{/case}
		{case}
			{"Undefined error."|i18n('design/exam')}
		{/case}

	{/switch}
	</div>
{/foreach}
</div>
