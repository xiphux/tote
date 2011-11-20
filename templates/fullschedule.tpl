{include file='header.tpl' header='Game Schedule' homelink=true}

<div>
<strong>{$year}-{$year+1}</strong><br />
<strong>By Week</strong> By Team
</div>

<table>
<tr>
<td>

{foreach from=$games item=weekgames key=week}
<a href="#week{$week}">Week {$week}</a><br />
{/foreach}

</td>
<td>

{foreach from=$games item=weekgames key=week}
<div class="divScheduleWeek" id="week{$week}">
<strong>Week {$week}</strong><br />

{foreach name=weekgames from=$weekgames item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}
{if $smarty.foreach.weekgames.first}</div>{/if}
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
