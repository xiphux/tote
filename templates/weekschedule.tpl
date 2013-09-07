{if !$js}
{include file='header.tpl' header='View Game Schedule' homelink=true}

Games for {$year}-{$year+1} week {$week}:
{/if}

<div>
{foreach name=games from=$games item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}
{if !$smarty.foreach.games.first}</div>{/if}
<div class="divScheduleDay">
<time datetime="{$game.localstart->format('Y-m-d')}">{$day}</time>:
{assign var=lastday value=$day}{/if}
<br /><span {if $game.start->sec < $smarty.now}class="gamestarted"{/if}>{$game.away_team.abbreviation} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_team.abbreviation} {if isset($game.home_score)}{$game.home_score}{/if} at <time datetime="{$game.localstart->format('Y-m-d\TH:i:sO')}">{$game.localstart->format('g:i a T')}</time></span>
{/foreach}
</div>
</div>

{if !$js}
{include file='footer.tpl'}
{/if}
