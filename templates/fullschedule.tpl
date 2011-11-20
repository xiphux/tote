{include file='header.tpl' header='Game Schedule' homelink=true}

<div>
<h2>{$year}-{$year+1}</h2>
<div class="scheduleTabs">
<span class="activeTab">By Week</span> <a href="{$SCRIPT_NAME}?a=teamschedule&y={$year}">By Team</a>
</div>
</div>

<table class="scheduleTable">
<tr>
<td class="scheduleToc">
<div>
{foreach from=$games item=weekgames key=week}
<a href="#week{$week}">Week {$week}</a><br />
{/foreach}
</div>
</td>
<td class="scheduleContent">

{foreach from=$games item=weekgames key=week}
<div class="divScheduleWeek" id="week{$week}">
<div class="scheduleSubHeader">Week {$week}</div>

{foreach name=weekgames from=$weekgames item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}
{if !$smarty.foreach.weekgames.first}</div>{/if}
<div class="divScheduleDay">
{$day}:
{assign var=lastday value=$day}{/if}
<br />{$game.away_team.abbreviation} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_team.abbreviation} {if isset($game.home_score)}{$game.home_score}{/if} at {$game.localstart->format('g:i a T')}
{/foreach}
</div>

</div>
{/foreach}

</td>
</tr>
</table>

{include file='footer.tpl'}
