{include file='header.tpl' header='Analytics' homelink=true source='analytics'}

<div class="navTabs">
{if $graphtype == 'pickrisk'}
<span class="activeTab">Pick Risk</span>
{else}
<a href="{$SCRIPT_NAME}?a=analytics&g=pickrisk">Pick Risk</a>
{/if}
|
{if $graphtype == 'pickdist'}
<span class="activeTab">Pick Distribution</span>
{else}
<a href="{$SCRIPT_NAME}?a=analytics&g=pickdist">Pick Distribution</a>
{/if}
|
{if $graphtype == 'teamrel'}
<span class="activeTab">Team Relationships</span>
{else}
<a href="{$SCRIPT_NAME}?a=analytics&g=teamrel">Team Relationships</a>
{/if}
</div>

<div id="graphControls">
</div>
<div id="graph">
</div>

{include file='footer.tpl'}
