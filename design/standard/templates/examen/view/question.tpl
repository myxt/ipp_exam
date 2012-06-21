<div class="exam-view">
    <div class="question control-group">
        <label class="text control-label">
            {$element.getXMLContent}
        </label>
        {if ne($random,"false")}
            {def $answers=$element.randomAnswers}
        {else}
            {def $answers=$element.answers}
        {/if}
        <div class="answers controls">
            <input type="hidden" name="answer_{$element.id}" value="0">
            {foreach $element.randomAnswers as $answer}
                <label class="radio" for="answer_{$element.id}">
                    <input type="radio" name="answer_{$element.id}" value="{$answer.id}"> {$answer.content}
                </label>
            {/foreach}
        </div>
    </div>
</div>
