{include file='header.tpl' header='Game Schedule' homelink=true}

<div>
<h2>{$year}-{$year+1}</h2>
<div class="scheduleTabs">
<a href="{$SCRIPT_NAME}?a=schedule&y={$year}">By Week</a> <span class="activeTab">By Team</span>
</div>
</div>

<table class="scheduleTable">
<tr>
<td class="scheduleToc">

{foreach from=$games item=teamgames key=team}
<a href="#{$team}">{$teamnames.$team}</a><br />
{/foreach}

</td>
<td class="scheduleContent">

{foreach from=$games item=teamgames key=team}
<div class="divScheduleTeam" id="{$team}">
<div class="scheduleSubHeader">{$teamnames.$team}</div>
<table>
{foreach from=$teamgames item=game key=eachweek}
<tr>
<td>
Week {$eachweek}:
</td>
<td>
{if $game.bye}
Bye
{else}
{$game.away_team.abbreviation} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_team.abbreviation} {if isset($game.home_score)}{$game.home_score}{/if}
{/if}
</td>
<td>
{if !$game.bye}
{$game.localstart->format('D M j, Y g:i a T')}
{/if}
</td>
</tr>
{/foreach}
</table>
</div>
{/foreach}

{include file='footer.tpl'}
