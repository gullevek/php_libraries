<div>
	{$SMARTY_TEST}
</div>
<div>
	<select id="drop_down_test" name="drop_down_test">
		{html_options options=$drop_down_test selected=$drop_down_test_selected}
	</select>
</div>
<div class="jq-container">
	<div id="jq-test" class="jp-test">
		<div id="test-div" class="test-div">
			Some content ehre or asdfasdfasf
		</div>
	</div>
</div>
{* progresss indicator *}
<div id="indicator"></div>
{* the action confirm box *}
<div id="actionBox" class="actionBoxElement"></div>
{* The Overlay box *}
<div id="overlayBox" class="overlayBoxElement"></div>
