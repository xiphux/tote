{if !$js}
{include file='header.tpl' header='View Team Schedule' homelink=true}

Games for {$year}-{$year+1} for the {$team.home} {$team.team}:
{/if}

<div>
<table>
{foreach name=games from=$games item=game key=eachweek}
<tr class="{if $game.bye || $game.start->sec < $smarty.now}gamestarted{/if} {if $eachweek == $week}currentgame{/if}">
<td>
Week {$eachweek}:
</td>
<td>
{if $game.bye}
Bye
{else}
{assign var=day value=$game.localstart->format('D M j, Y')}
<span>{$game.away_team.abbreviation} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_team.abbreviation} {if isset($game.home_score)}{$game.home_score}{/if}</span>
{/if}
</td>
</tr>
{/foreach}
</table>
</div>

{if !$js}
{include file='footer.tpl'}
{/if}
