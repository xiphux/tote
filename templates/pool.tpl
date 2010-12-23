{include file='header.tpl' poolinfo=$pool source='pool'}

<div class="poolInfoDiv">
<div class="poolNavDiv">
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
{if $pool.fee}
<br />Entry fee: ${$pool.fee}
{if $payout}
<br />1st place: ${$payout.1}
<br />2nd place: ${$payout.2}
<br />3rd place: ${$payout.3}
{/if}
{/if}
</div>
{if $user && $entered && $poolopen}
<div class="poolBetDiv">
<form action="index.php" method="get">
<label for="bet">Bet on week:</label> 
<select name="w">
{foreach from=$weeks key=wknum item=open}
  {if $open}
    <option value="{$wknum}">Week {$wknum}</option>
  {/if}
{/foreach}
</select>
<input type="hidden" name="a" value="bet" />
<input type="hidden" name="p" value="{$pool._id}" />
<input value="Bet" type="submit" />
</form>
</div>
{/if}
</div>

<div class="userOpts">
<table class="displayTable">
{if $user}
<thead>
  <tr><th>Welcome, {if $user.first_name}{$user.first_name} {$user.last_name}{else}{$user.username}{/if}</th></tr>
</thead>
<tbody>
{if $user.admin}
<tr><td><a href="index.php?a=update">Update scores</a></td></tr>
<tr><td><a href="index.php?a=editpool&p={$pool._id}">Manage pool</a></td></tr>
<tr><td><a href="index.php?a=editusers">Manage users</a></td></tr>
<tr><td><a href="index.php?a=newpool">New pool</a></td></tr>
{/if}
<tr><td><a href="index.php?a=editprefs">Edit preferences</a></td></tr>
<tr><td><a href="index.php?a=changepass">Change password</a></td></tr>
<tr><td><a href="index.php?a=logout">Logout</a></td></tr>
{else}
<tr><td><a href="index.php?a=login">Login</a></td></tr>
{/if}
</tbody>
</table>
</div>

<div class="clear">&nbsp;</div>

<table class="scoreTable displayTable">

<thead>
<th>Name</th>
<th title="Wins">W</th>
<th title="Losses">L</th>
<th title="Point Spread">PS</th>
{foreach from=$weeks key=wknum item=open}
<th title="Week {$wknum}"{if $wknum == $currentweek} class="currentweek"{elseif !$weeks.$wknum} class="weekclosed"{/if}>W{$wknum}</th>
{/foreach}
</thead>

<tbody>

{foreach from=$record item=entrant}

<tr class="{cycle values=light,dark} {if $user._id == $entrant.user._id}self{/if}">
<td class="entrantName">
{if $user.admin}
<a href="index.php?a=editbets&p={$pool._id}&u={$entrant.user._id}" title="Edit {if $entrant.user.first_name}{$entrant.user.first_name}{if $entrant.user.last_name} {$entrant.user.last_name}{/if}{else}{$entrant.user.username}{/if}'s bets">
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
<a href="{$SCRIPT_NAME}?a=bet&p={$pool._id}&w={$betweek}" class="betLink">Bet</a>
{/if}
 {if $bet.spread}({$bet.spread}){/if}
</span>
</td>
{/foreach}

</tr>

{/foreach}

</tbody>

</table>

<div class="poolFooter">

<div class="poolRules">
	<a id="lnkRules" href="{$SCRIPT_NAME}?a=rules">Rules</a>
</div>

<div class="poolHistory">
<a id="lnkHistory" title="View history of events for this pool" href="{$SCRIPT_NAME}?a=history&p={$pool._id}">History</a>
<a class="feedTip" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&p={$pool._id}"><img src="images/feed-icon-14x14.png" width="14" height="14" /></a>
</div>

<div class="clear">
</div>

</div>

{include file='footer.tpl'}
