{if !$js}
{include file='header.tpl' header='View Team Schedule' homelink=true}

Games for {$year}-{$year+1} for the {$team.home} {$team.team}:
{/if}

<div>
<table>
{foreach name=games from=$games item=game key=eachweek}
<tr class="{if $game.bye || $game.start < $smarty.now}gamestarted{/if} {if $eachweek == $week}currentgame{/if}">
<td>
Week {$eachweek}:
</td>
<td>
{if $game.bye}
Bye
{else}
<span>{$game.away_team_abbr} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_team_abbr} {if isset($game.home_score)}{$game.home_score}{/if}</span>
{/if}
</td>
</tr>
{/foreach}
</table>
</div>

{if !$js}
{include file='footer.tpl'}
{/if}
