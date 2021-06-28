<div>
	{$SMARTY_TEST}
</div>
<div>
	TRANSLATION CLASS (OUT): {$TRANSLATE_TEST}
</div>
<div>
	TRANSLATION CLASS (SMARTY): {$TRANSLATE_TEST_SMARTY}
</div>
<div>
	<select id="drop_down_test" name="drop_down_test">
		{html_options options=$drop_down_test selected=$drop_down_test_selected}
	</select>
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
	<div>LOOP TEST</div>
{section name=page_list start=1 loop=$loop_start+1}
	<div>LOOP OUTPUT: {$smarty.section.page_list.index}</div>
{/section}
</div>
{* progresss indicator *}
<div id="indicator"></div>
{* the action confirm box *}
<div id="actionBox" class="actionBoxElement"></div>
{* The Overlay box *}
<div id="overlayBox" class="overlayBoxElement"></div>
