{include file='header.tpl' header='Game Schedule' homelink=true source='gridschedule'}

<div class="scheduleNav">
{if $allseasons && (count($allseasons) > 1)}
<form action="index.php" method="get">
<select id="seasonSelect" name="y">
{foreach from=$allseasons item=eachseason}
<option value="{$eachseason}" {if $year == $eachseason}selected="selected"{/if}>{$eachseason}-{$eachseason+1}</option>
{/foreach}
</select>
<input type="hidden" name="a" value="gridschedule" />
<input type="submit" value="Go" id="seasonSubmit" />
</form>
{else}
<strong>{$year}-{$year+1}</strong>
{/if}
</div>
<div class="scheduleTabs">
<a href="{$SCRIPT_NAME}?a=schedule&y={$year}">By Week</a> | <a href="{$SCRIPT_NAME}?a=teamschedule&y={$year}">By Team</a> | <span class="activeTab">Grid</span>
</div>

<table class="displayTable gridSchedule">
 <tr>
  <th>Team</th>
  {foreach from=$openweeks key=wknum item=open}
  <th{if !$open} class="weekclosed"{/if} title="Week {$wknum}">W{$wknum}</th>
  {/foreach}
 </tr>
 {foreach from=$games item=teamgames key=team}
  <tr class="{cycle values=light,dark}">
    <td><strong>{$teamabbrs.$team}</strong></td>
    {foreach from=$teamgames key=eachweek item=game}
     <td{if !$game.bye} title="{$game.away_team.abbreviation} @ {$game.home_team.abbreviation}"{/if}>
     {if $game.bye}
       Bye
     {elseif $game.away_team._id == $team}
       @{$game.home_team.abbreviation}
     {else}
      {$game.away_team.abbreviation}
     {/if}
     </td>
    {/foreach}
  </tr>
 {/foreach}
</table>

{include file='footer.tpl'}
