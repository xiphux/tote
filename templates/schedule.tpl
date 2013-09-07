{include file='header.tpl' header='Game Schedule' homelink=true jsmodule='fullschedule'}

<div class="scheduleNav">
{if $allseasons && (count($allseasons) > 1)}
<form action="index.php" method="get">
<select id="seasonSelect" name="y">
{foreach from=$allseasons item=eachseason}
<option value="{$eachseason}" {if $year == $eachseason}selected="selected"{/if}>{$eachseason}-{$eachseason+1}</option>
{/foreach}
</select>
<input type="hidden" name="a" value="schedule" />
<input type="submit" value="Go" id="seasonSubmit" />
</form>
{else}
<strong>{$year}-{$year+1}</strong>
{/if}
</div>
<div class="navTabs">
<span class="activeTab">By Week</span> | <a href="{$SCRIPT_NAME}?a=teamschedule&amp;y={$year}">By Team</a> | <a href="{$SCRIPT_NAME}?a=gridschedule&amp;y={$year}">Grid</a>
</div>

<table class="scheduleTable">
<tr>
<td class="scheduleToc">
<div class="scheduleTocContent">
<ul>
{foreach from=$games item=weekgames key=week}
<li><a href="#week{$week}">Week {$week}</a></li>
{/foreach}
</ul>
</div>
</td>

{if $mobile}
</tr>
<tr>
{/if}

<td class="scheduleContent weekScheduleContent">

{foreach from=$games item=weekgames key=week}
<div class="divScheduleWeek divScheduleItem" id="week{$week}">
<div class="scheduleSubHeader">Week {$week}</div>

{foreach name=weekgames from=$weekgames item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}
{if !$smarty.foreach.weekgames.first}</div>{/if}
<div class="divScheduleDay">
<time datetime="{$game.localstart->format('Y-m-d')}">{$day}</time>:
{assign var=lastday value=$day}{/if}
<br />{$game.away_abbr} {if isset($game.away_score)}{$game.away_score}{/if} @ {$game.home_abbr} {if isset($game.home_score)}{$game.home_score}{/if} at <time datetime="{$game.localstart->format('Y-m-d\TH:i:sO')}">{$game.localstart->format('g:i a T')}</time>
{/foreach}
</div>

</div>
{/foreach}

</td>
</tr>
</table>

{include file='footer.tpl'}
