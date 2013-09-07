{include file='header.tpl' header='Game Schedule' homelink=true jsmodule='fullschedule'}

<div class="scheduleNav">
{if $allseasons && (count($allseasons) > 1)}
<form action="index.php" method="get">
<select id="seasonSelect" name="y">
{foreach from=$allseasons item=eachseason}
<option value="{$eachseason}" {if $year == $eachseason}selected="selected"{/if}>{$eachseason}-{$eachseason+1}</option>
{/foreach}
</select>
<input type="hidden" name="a" value="teamschedule" />
<input type="submit" value="Go" id="seasonSubmit" />
</form>
{else}
<strong>{$year}-{$year+1}</strong>
{/if}
</div>
<div class="navTabs">
<a href="{$SCRIPT_NAME}?a=schedule&amp;y={$year}">By Week</a> | <span class="activeTab">By Team</span> | <a href="{$SCRIPT_NAME}?a=gridschedule&amp;y={$year}">Grid</a>
</div>

<table class="scheduleTable">
<tr>
<td class="scheduleToc">

<div class="scheduleTocContent">
<ul>
{foreach from=$games item=teamgames key=team}
<li><a href="#{$team}">{$teamnames.$team}</a></li>
{/foreach}
</ul>
</div>

</td>

{if $mobile}
</tr>
<tr>
{/if}

<td class="scheduleContent teamScheduleContent">

{foreach from=$games item=teamgames key=team}
<div class="divScheduleTeam divScheduleItem" id="{$team}">
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
<time datetime="{$game.localstart->format('Y-m-d\TH:i:sO')}">{$game.localstart->format('D M j, Y g:i a T')}</time>
{/if}
</td>
</tr>
{/foreach}
</table>
</div>
{/foreach}

</td>
</tr>
</table>

{include file='footer.tpl'}
