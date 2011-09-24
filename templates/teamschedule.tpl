{if !$js}
{include file='header.tpl' header='View Team Schedule' homelink=true}

Games for {$year}-{$year+1} for the {$team.home} {$team.team}:
{/if}

<div>
<table>
{foreach name=games from=$games item=game key=week}
<tr>
<td>
<span {if $game.bye || $game.start->sec < $smarty.now}class="gamestarted"{/if}>
Week {$week}:
</span>
</td>
<td>
{if $game.bye}
<span class="gamestarted">
Bye
</span>
{else}
{assign var=day value=$game.localstart->format('D M j, Y')}
<span {if $game.start->sec < $smarty.now}class="gamestarted"{/if}>{$game.away_team.abbreviation} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_team.abbreviation} {if isset($game.home_score)}{$game.home_score}{/if}</span>
{/if}
</td>
</tr>
{/foreach}
</table>
</div>

{if !$js}
{include file='footer.tpl'}
{/if}
