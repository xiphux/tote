{include file='header.tpl' header='Team Schedule' homelink=true}

<div>
<strong>{$year}-{$year+1}</strong><br />
<a href="{$SCRIPT_NAME}?a=schedule&y={$year}">By Week</a> <strong>By Team</strong>
</div>

<table>
<tr>
<td>

{foreach from=$games item=teamgames key=team}
<a href="#{$team}">{$teamnames.$team}</a><br />
{/foreach}

</td>
<td>

{foreach from=$games item=teamgames key=team}
<div class="divScheduleTeam" id="{$team}">
<strong>{$teamnames.$team}</strong>
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
