{include file='header.tpl' poolinfo=$pool source='pool'}

<div class="{if !$mobile || $forcefull}poolInfoDiv{else}poolInfoSingleDiv{/if}">

<table class="displayTable infoTable subSection rounded-top rounded-bottom subShadow">
<tr>
<th colspan="2">
{if $allpools && (count($allpools) > 1)}
<form action="index.php" method="get">
<select id="poolNameSelect" name="p">
{foreach from=$allpools item=eachpool}
<option value="{$eachpool._id}" {if $eachpool._id == $pool._id}selected="selected"{/if}>{$eachpool.name} [{$eachpool.season}-{$eachpool.season+1}]</option>
{/foreach}
</select>
<input type="submit" value="Go" id="poolNameSubmit" />
</form>
{else}
{$pool.name} [{$pool.season}-{$pool.season+1}]
{/if}
</th>
</tr>
{if $mobile && !$forcefull}
{include file='usermenu.tpl'}
{/if}
</table>

{if $user && $entered && $poolopen}
<div class="poolBetDiv">
<form action="index.php" method="get">
<label for="bet">Pick for week:</label> 
<select name="w">
{foreach from=$weeks key=wknum item=open}
  {if $open}
    <option value="{$wknum}">Week {$wknum}</option>
  {/if}
{/foreach}
</select>
<input type="hidden" name="a" value="bet" />
<input type="hidden" name="p" value="{$pool._id}" />
<input value="Pick" type="submit" />
</form>
</div>
{/if}

</div>

{if !$mobile || $forcefull}

<div class="userOpts">
<table class="displayTable subSection rounded-top rounded-bottom subShadow">
{include file='usermenu.tpl'}
</table>
</div>

{/if}

<div class="clear">&nbsp;</div>

<div id="scoreTicker" class="rounded-top subShadow"></div>

<div id="poolMain" class="rounded-bottom rounded-top subShadow">

<table class="scoreTable displayTable">

<thead>
<th>Name</th>
<th title="Wins">W</th>
<th title="Losses">L</th>
<th title="Point Differential">PD</th>

{foreach from=$weeks key=wknum item=open}

{if !$mobile || $forcefull || (array_search($wknum,$mobileweeks) !== false)}

<th{if $wknum == $currentweek} class="currentweek"{elseif !$weeks.$wknum} class="weekclosed"{/if}><a class="scheduleLink" title="View week {$wknum} schedule" href="{$SCRIPT_NAME}?a=schedule&y={$pool.season}&w={$wknum}">W{$wknum}</a></th>

{/if}

{/foreach}
</thead>

<tbody>

{foreach from=$record item=entrant}

<tr class="{cycle values=light,dark} {if $user._id == $entrant.user._id}self{/if}">
<td class="entrantName">
{if $user.admin}
<a href="index.php?a=editbets&p={$pool._id}&u={$entrant.user._id}" title="Edit {if $entrant.user.first_name}{$entrant.user.first_name}{if $entrant.user.last_name} {$entrant.user.last_name}{/if}{else}{$entrant.user.username}{/if}'s picks">
{/if}
{if $entrant.user.first_name}{$entrant.user.first_name}{if $entrant.user.last_name} {$entrant.user.last_name}{/if}{else}{$entrant.user.username}{/if}
{if $user.admin}
</a>
{/if}
</td>

<td>{$entrant.wins}</td>
<td>{$entrant.losses}</td>
<td>{$entrant.spread}</td>

{foreach from=$entrant.bets key=betweek item=bet}

{if !$mobile || $forcefull || (array_search($betweek,$mobileweeks) !== false)}
<td>
<span 
{if $bet.result > 0}class="win"{elseif $bet.result < 0}class="loss"{/if}
{if $bet.game}title="{$bet.game.away_team.abbreviation}{if $bet.game.away_score} {$bet.game.away_score}{/if} @ {$bet.game.home_team.abbreviation}{if $bet.game.home_score} {$bet.game.home_score}{/if}"{/if}
>
{if $bet.team.abbreviation}
{$bet.team.abbreviation}
{elseif $bet.nopick}
-NP-
{elseif $user && $entered && $poolopen && ($user._id == $entrant.user._id)}
<a href="{$SCRIPT_NAME}?a=bet&p={$pool._id}&w={$betweek}" class="betLink">Pick</a>
{/if}
 {if $bet.spread}({$bet.spread}){/if}
</span>
</td>
{/if}

{/foreach}

</tr>

{/foreach}

</tbody>

</table>

<div class="poolFooter">

<div class="poolRules">
	<a id="lnkRules" href="{$SCRIPT_NAME}?a=rules&p={$pool._id}">Rules</a>
</div>

<div class="poolHistory">
<a id="lnkHistory" title="View history of events for this pool" href="{$SCRIPT_NAME}?a=history&p={$pool._id}">History</a>
<a class="feedTip" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&p={$pool._id}"><img src="images/feed-icon-14x14.png" width="14" height="14" /></a>
</div>

<div class="clear">
</div>

</div>

</div>

{if $mobile}
<div>
{if $forcefull}
<a href="{$SCRIPT_NAME}?p={$pool._id}&full=0">Switch to mobile version</a>
{else}
<a href="{$SCRIPT_NAME}?p={$pool._id}&full=1">Switch to full version</a>
{/if}
</div>
{/if}

{if $links}
<div id="linksDiv" class="subSection rounded-top rounded-bottom subShadow">

<div id="linksToggle">
<span id="spanLinks">Useful links:</span>
</div>

<div id="linksList">
<ul>
{foreach from=$links item=url key=name}
<li><a href="{$url}" target="_blank">{$name}</a></li>
{/foreach}
</ul>
</div>

</div>
{/if}

{include file='footer.tpl'}
