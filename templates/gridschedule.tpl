{include file='header.tpl' header='Game Schedule' homelink=true jsmodule='gridschedule'}

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
<div class="navTabs">
<a href="{$SCRIPT_NAME}?a=schedule&amp;y={$year}">By Week</a> | <a href="{$SCRIPT_NAME}?a=teamschedule&amp;y={$year}">By Team</a> | <span class="activeTab">Grid</span>
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
    <td><strong>{$team}</strong></td>
    {foreach from=$teamgames key=eachweek item=game}
     <td {if $game}class="gridGame" data-start="{$game.start->format('D M j, Y g:i a T')}" data-startstamp="{$game.start->format('Y-m-d\TH:i:sO')}" data-game="{$game.away_abbr}{if isset($game.away_score)} {$game.away_score}{/if} @ {$game.home_abbr}{if isset($game.home_score)} {$game.home_score}{/if}"{/if}>
     {if !$game}
       <span class="gridBye">Bye</span>
     {elseif $game.away_abbr == $team}
       {if isset($game.home_score) && isset($game.away_score)}
         {if $game.away_score > $game.home_score}
           <span class="gridWin">@{$game.home_abbr}</span>
         {elseif $game.home_score > $game.away_score}
           <span class="gridLoss">@{$game.home_abbr}</span>
         {elseif $game.home_score == $game.away_score}
           <span class="gridTie">@{$game.home_abbr}</span>
         {/if}
       {else}
         @{$game.home_abbr}
       {/if}
     {else}
       {if isset($game.home_score) && isset($game.away_score)}
         {if $game.home_score > $game.away_score}
           <span class="gridWin">{$game.away_abbr}</span>
         {elseif $game.away_score > $game.home_score}
           <span class="gridLoss">{$game.away_abbr}</span>
         {elseif $game.home_score == $game.away_score}
           <span class="gridTie">{$game.away_abbr}</span>
         {/if}
       {else}
         {$game.away_abbr}
       {/if}
     {/if}
     </td>
    {/foreach}
  </tr>
 {/foreach}
</table>

{include file='footer.tpl'}
