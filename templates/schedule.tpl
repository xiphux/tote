{if !$js}
{include file='header.tpl'}
{/if}

{if !$js}
Games for {$year}-{$year+1} week {$week}:
{/if}

<div>
{foreach name=games from=$games item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}
{if !$smarty.foreach.games.first}</div>{/if}
<div class="divScheduleDay">
{$day}:
{assign var=lastday value=$day}{/if}
<br /><span {if $game.start->sec < $smarty.now}class="gamestarted"{/if}>{$game.away_team.abbreviation} {if $game.away_score}{$game.away_score}{/if} @ {$game.home_team.abbreviation} {if $game.home_score}{$game.home_score}{/if} at {$game.localstart->format('g:i a T')}</span>
{/foreach}
</div>
</div>

{if !$js}
{include file='footer.tpl'}
{/if}
