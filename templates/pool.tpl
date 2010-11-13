{include file='header.tpl'}

<div>
{$pool.name} [{$pool.season}-{$pool.season+1}]<br />
Entry fee: ${$pool.fee}
</div>

<div>
</div>

<div>
</div>

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

<tr class="{cycle values=light,dark}">

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
