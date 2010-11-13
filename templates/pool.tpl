{include file='header.tpl'}

<div class="poolInfoDiv">
{$pool.name} [{$pool.season}-{$pool.season+1}]<br />
Entry fee: ${$pool.fee}
{if $user && $entered && $poolopen}
<p>
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
</p>
{/if}
</div>

<div class="userOptsDiv">
{if $user}
Welcome, {if $user.first_name}{$user.first_name} {$user.last_name}{else}{$user.username}{/if}<br />
{if $user.admin}
<a href="index.php?a=update">Update scores</a><br />
{/if}
<a href="index.php?a=changepass">Change password</a><br />
<a href="index.php?a=logout">Logout</a>
{else}
<a href="index.php?a=login">Login</a>
{/if}
</div>

<div class="clear">&nbsp;</div>

<table class="scoreTable">

<thead>
<th>Name</th>
<th title="Wins">W</th>
<th title="Losses">L</th>
<th title="Point Spread">PS</th>
{foreach from=$weeks key=wknum item=open}
<th title="Week {$wknum}">W{$wknum}</th>
{/foreach}
</thead>

<tbody>

{foreach from=$record item=entrant}

<tr class="{cycle values=light,dark} {if $user._id == $entrant.user._id}self{/if}">

<td>{if $entrant.user.first_name}{$entrant.user.first_name} {$entrant.user.last_name}{else}{$entrant.user.username}{/if}</td>

<td>{$entrant.wins}</td>
<td>{$entrant.losses}</td>
<td>{$entrant.spread}</td>

{foreach from=$entrant.bets item=bet}
<td>
<span {if $bet.result > 0}class="win"{elseif $bet.result < 0}class="loss"{/if}>
{if $bet.team.abbreviation}
{$bet.team.abbreviation}
{elseif $bet.nopick}
No Pick
{/if}
 {if $bet.spread}({$bet.spread}){/if}
</span>
</td>
{/foreach}

</tr>

{/foreach}

</tbody>

</table>

{include file='footer.tpl'}
