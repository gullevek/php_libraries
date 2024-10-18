<div>
	SMARTY_TEST: {$SMARTY_TEST}
</div>
<div>
	MERGE DATA: {$adm_set}
</div>
<div {popup width="250" caption="Info" text="Text block<br>Control"} style="border: 1px solid black; margin: 5px 0 5px 0; padding: 5px;">
	POPUP HERE (hover mouse)
</div>
<div>
	<b>Outside translation test</b><br>
	TRANSLATION CLASS (OUT): {$TRANSLATE_TEST}<br>
	TRANSLATION CLASS (OUT FUNCTION): {$TRANSLATE_TEST_FUNCTION}<br>
	TRANSLATION CLASS (SMARTY): {$TRANSLATE_TEST_SMARTY}<br>
</div>
<div>
	<b>Translate Test with replace:</b><br>
	ORIGINAL: Original with string: %1 ({$replace})<br>
	TRANSLATED: {t 1=$replace}Original with string: %1{/t}<br>
	TRANSLATED (escape): {t escape=on 1=$replace}Original with string: %1{/t}<br>
	{capture assign="extra_title"}{t}INPUT TEST{/t}{/capture}
	Capture test: {$extra_title}<br>
	{section name=plural_test start=0 loop=3}
	Plural test {$smarty.section.plural_test.index}: {t count=$smarty.section.plural_test.index plural="multi"}single{/t}<br>
	{/section}
</div>
<div>
	<b>Variable variables:</b><br>
	Test: {$test}<br>
	Foo: {$foo}<br>
	{assign var="bar" value="test"}
	vFoo ($test = $foo = bar): {$test|getvar}<br>
	vFoo ($bar = $test = foo): {$bar|getvar}
</div>
<div class="jq-container">
	<div id="jq-test" class="jp-test">
		<div id="test-div" class="test-div">
			Some content here or asdfasdfasf
		</div>
		<div id="translate-div">
			TRANSLATION SMARTY: {t}I should be translated{/t}
		</div>
	</div>
</div>
<div class="loop-test">
	<div><b>LOOP TEST</b></div>
{section name=page_list start=1 loop=$loop_start+1}
	<div>LOOP OUTPUT: {$smarty.section.page_list.index}</div>
{/section}
</div>
<div>
	<select id="drop_down_test" name="drop_down_test">
		{html_options options=$drop_down_test selected=$drop_down_test_selected}
	</select>
</div>
<div>
	<select id="drop_down_test_nested" name="drop_down_test_nested">
		{html_options options=$drop_down_test_nested selected=$drop_down_test_nested_selected}
	</select>
</div>
<div>
	{html_radios name="radio_test" options=$radio_test selected=$radio_test_selected}
</div>
<div>
	{html_checkboxes name="checkbox_test" options=$checkbox_test selected=$checkbox_test_selected}
</div>
<div>
	{html_checkboxes name="checkbox_test_pos" options=$checkbox_test selected=$checkbox_test_pos_selected pos=$checkbox_test_pos}
</div>
{* progresss indicator *}
<div id="indicator"></div>
{* the action confirm box *}
<div id="actionBox" class="actionBoxElement"></div>
{* The Overlay box *}
<div id="overlayBox" class="overlayBoxElement"></div>
