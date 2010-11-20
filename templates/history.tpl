{include file='header.tpl' poolinfo=$pool}

<div>
{$pool.name} [{$pool.season}-{$pool.season+1}] History
</div>

<table class="historyTable displayTable">
<thead>
<th>Timestamp</th>
<th>User</th>
<th>Admin</th>
<th>Week</th>
<th>Event</th>
</thead>
{foreach from=$actions key=time item=timeactions}

{foreach from=$timeactions item=action}
<tr class="{cycle values=light,dark}">
  <td>{$action.time->format('r')}</td>
  <td>{if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}</td>
  <td>{if $action.admin}{if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if}{/if}</td>
  <td class="center">{$action.week}</td>
  <td>
  {if $action.action == 'bet'}
  Bet added: {$action.team.abbreviation}
  {elseif $action.action == 'edit'}
    {if $action.from_team && $action.to_team}
    Admin changed bet from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}
    {elseif $action.from_team}
    Admin deleted bet on {$action.from_team.abbreviation}
    {elseif $action.to_team}
    Admin added bet on {$action.to_team.abbreviation}
    {/if}
  {/if}
  </td>
</tr>
{/foreach}

{/foreach}
</table>

{include file='footer.tpl'}
