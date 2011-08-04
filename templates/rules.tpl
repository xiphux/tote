{if !$js}
{include file='header.tpl' header='Rules' homelink=true}
{/if}

<div>

<ul class="ruleList">

<li>Pick one team each week.</li>

<li>You can only pick each team once for the entire season.  Once a team has been picked, the website will not allow you to pick that team again.</li>

<li>At the end of the season, the person with the best record wins.  The point differential will be used as a tiebreaker.</li>

<li>A pick for a game must be submitted before the scheduled start of that game.  The website will not allow you to pick a game that has already started (according to the webserver's clock).</li>

<li>Failing to make a pick will result in a loss for that week.  Failing to make a pick during the last four weeks of the season will result in a 10 point penalty in addition to the loss.</li>

<li>The point differential is a running total of the point differential of each game you've bet on during the season.  If you win a game, the point differential is added to your total.  If you lose, the point differential is subtracted from your total.

{if $email}
<li>In the event of a technical difficulty, send an email to <a href="mailto:{$email}">{$email}</a> as soon as possible.</li>
{/if}

<li>You will be entered in the pool as soon as your entry fee is received.  If your payment is late, any weeks that have already passed at the time of entry will be counted as No Picks (losses).</li>

{if $admins}
<li>
If there is a dispute by a player, it will be discussed and a decision will be made by the administrative voting board.  {$admins.primary|@userlist} {if count($admins.primary) > 1}are the administrators{else}is the administrator{/if} for this pool.
{if $admins.secondary}
In the event that the main administrators cannot reach an agreement, {$admins.secondary|@userlist} will be the tiebreaker {if count($admins.secondary) > 1}administrators{else}administrator{/if} for the pool.
{/if}
</li>
{/if}

{if $pool.fee}
<li>Entry fee: ${$pool.fee|string_format:"%.2f"}</li>
{/if}

{if $payoutpercents}
<li>Payout: {foreach name=payoutpercents from=$payoutpercents key=place item=percent}{$place|place} place wins {$percent*100}%{if !$smarty.foreach.payoutpercents.last}, {/if}{/foreach}.  In the event of a tie, the tied players will split the sum of their payouts.</li>
{/if}

{if $payoutamounts}
<li>Current payout amounts:
<table>
{foreach from=$payoutamounts key=place item=amount}
<tr>
<td>{$place|place} place:</td>
<td>${$amount|string_format:"%.2f"}</td>
</tr>
{/foreach}
</table>
</li>
{/if}

</ul>

</div>

{if !$js}
{include file='footer.tpl'}
{/if}
