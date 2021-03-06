{if $mobile && !$forcefull}
{include file='header.tpl' poolinfo=$pool source='pool' jsmodule='poolmobile'}
{else}
{include file='header.tpl' poolinfo=$pool source='pool' jsmodule='pool'}
{/if}

<div class="{if !$mobile || $forcefull}poolInfoDiv{else}poolInfoSingleDiv{/if}">

<table class="displayTable infoTable subSection rounded-top rounded-bottom subShadow">
  <tr>
    <th>
    {if $allpools && (count($allpools) > 1)}
      <form action="index.php" method="get">
      <select id="poolNameSelect" name="p">
      {foreach from=$allpools item=eachpool}
        <option value="{$eachpool.id}" {if $eachpool.id == $pool.id}selected="selected"{/if}>{$eachpool.name} [{$eachpool.season}-{$eachpool.season+1}]</option>
      {/foreach}
      </select>
      <input type="submit" value="Go" id="poolNameSubmit" />
      </form>
    {else}
      {$pool.name} [{$pool.season}-{$pool.season+1}]
    {/if}
    </th>
  </tr>
  <tr>
    <td>
    <table class="poolAmounts">
    {if $pool.fee}
      <tr>
      <td class="label">Entry:</td>
      <td class="amount">${$pool.fee|string_format:"%.2f"}</td>
      </tr>
    {/if}
    {if $pot}
      <tr>
      <td class="label">Pot:</td>
      <td class="amount">${$pot|string_format:"%.2f"}</td>
      </tr>
    {/if}
    </table>
    </td>
  </tr>
  {if ($pool.fee || $pot) && $payoutamounts}
  <tr class="tableBreak"><td></td>
  </tr>
  {/if}
  {if $payoutamounts}
  <tr>
  <td>
    <table class="poolAmounts">
    {foreach from=$payoutamounts key=place item=amount}
    <tr>
    <td class="label">{$place|place}:</td>
    <td class="amount">${$amount|string_format:"%.2f"}</td>
    </tr>
    {/foreach}
    </table>
  </td>
  </tr>
  {/if}
{if $mobile && !$forcefull}
<tr class="tableBreak"><td></td>
</tr>
{include file='usermenu.tpl'}
{/if}
</table>

{if $user && $entered && $poolopen}
<div id="poolPickDiv" class="subSection rounded-top rounded-bottom subShadow">
<div id="poolPickHeader">
Make a Pick
</div>
<div id="poolPickContent">
<form action="index.php" method="get">
<label for="weekPick">Pick for week:</label> 
<select name="w" id="weekPick">
{foreach from=$weeks key=wknum item=open}
  {if $open}
    <option value="{$wknum}">Week {$wknum}</option>
  {/if}
{/foreach}
</select>
<input type="hidden" name="a" value="bet" />
<input type="hidden" name="p" value="{$pool.id}" />
<input value="Pick" type="submit" />
</form>
</div>
</div>
{/if}

</div>

{if !$mobile || $forcefull}

<div class="userOpts">
<table class="displayTable menuTable subSection rounded-top rounded-bottom subShadow">
{include file='usermenu.tpl'}
</table>
</div>

{/if}

<div class="clear">&nbsp;</div>

<div id="scoreTicker" class="rounded-top subShadow"></div>

<div id="poolMain" class="rounded-bottom rounded-top subShadow">

<table class="scoreTable displayTable">

<thead>
<tr>
<th>Name</th>
<th title="Wins">W</th>
<th title="Losses">L</th>
{if $showties}
<th title="Ties">T</th>
{/if}
<th title="Point Differential">PD</th>

{foreach from=$weeks key=wknum item=open}

{if !$mobile || $forcefull || (array_search($wknum,$mobileweeks) !== false)}

<th{if $wknum == $currentweek} class="currentweek"{elseif !$weeks.$wknum} class="weekclosed"{/if}><a class="scheduleLink" title="View week {$wknum} schedule" href="{$SCRIPT_NAME}?a=schedule&amp;y={$pool.season}&amp;w={$wknum}">W{$wknum}</a></th>

{/if}

{/foreach}
</tr>
</thead>

<tbody>

{foreach from=$record item=entrant}

<tr class="{cycle values=light,dark} {if $user.id == $entrant.user_id}self{/if}">
<td class="entrantName">
{if $user.role == 1}
<a href="index.php?a=editbets&amp;p={$pool.id}&amp;u={$entrant.user_id}" title="Edit {$entrant.display_name}'s picks">
{/if}
{$entrant.display_name}
{if $user.role == 1}
</a>
{/if}
</td>

<td>{$entrant.wins}</td>
<td>{$entrant.losses}</td>
{if $showties}
<td>{$entrant.ties}</td>
{/if}
<td>{$entrant.spread}</td>

{foreach from=$entrant.picks item=pick}

{if !$mobile || $forcefull || (array_search($pick.week,$mobileweeks) !== false)}
<td>

{if !$pick.open || $pick.pick_team_id}
<span 
{if $pick.win}class="win"{elseif $pick.loss}class="loss"{elseif $pick.tie}class="tie"{/if}
{if $pick.game_id} title="{$pick.away_team_abbr}{if !empty($pick.away_score)} {$pick.away_score}{/if} @ {$pick.home_team_abbr}{if !empty($pick.home_score)} {$pick.home_score}{/if}"{/if}
>
{if $pick.pick_team_abbr}
{$pick.pick_team_abbr}
{elseif empty($pick.pick_team_id) && $pick.loss}
-NP-
{/if}
 {if !empty($pick.spread) || $pick.spread=="0"}({$pick.spread}){/if}
</span>
{elseif $user && $entered && $poolopen && ($user.id == $entrant.user_id)}
<span>
<a href="{$SCRIPT_NAME}?a=bet&amp;p={$pool.id}&amp;w={$pick.week}" class="betLink">Pick</a>
</span>
{/if}

</td>
{/if}

{/foreach}

</tr>

{/foreach}

</tbody>

</table>

<div class="poolFooter">

<div class="poolRules">
	<a id="lnkRules" href="{$SCRIPT_NAME}?a=rules&amp;p={$pool.id}">Rules</a>
</div>

{if !$mobile || $forcefull}
<span class="entrantCount">{$record|@count} entrants</span>
{/if}

<div class="poolHistory">
<a id="lnkHistory" title="View history of events for this pool" href="{$SCRIPT_NAME}?a=history&amp;p={$pool.id}">History</a>
<a class="feedTip" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&amp;p={$pool.id}"><img class="feedIcon" src="images/feed-icon-14x14.png" width="14" height="14" alt="Feed" /></a>
</div>

<div class="clear">
</div>

</div>

</div>

{if $mobile}
<div>
{if $forcefull}
<a href="{$SCRIPT_NAME}?p={$pool.id}&full=0">Switch to mobile version</a>
{else}
<a href="{$SCRIPT_NAME}?p={$pool.id}&full=1">Switch to full version</a>
{/if}
</div>
{/if}

{if $links}
<div id="linksSection" class="subSection rounded-top rounded-bottom subShadow">

<div class="sectionHeader">
Useful links
</div>

<div class="sectionContent">
<ul>
{foreach from=$links item=url key=name}
<li><a href="{$url}" target="_blank">{$name}</a></li>
{/foreach}
</ul>
</div>

</div>
{/if}

{include file='footer.tpl' showattr=true}
