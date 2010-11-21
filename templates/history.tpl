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
  <td>{$action.user_name}</td>
  <td>{if $action.admin_name}{$action.admin_name}{/if}</td>
  <td class="center">{if $action.week && ($action.week > 0)}{$action.week}{/if}</td>
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
  {elseif $action.action == 'addentrant'}
    Admin added user to pool
  {elseif $action.action == 'removeentrant'}
    Admin removed user from pool
  {/if}
  </td>
</tr>
{/foreach}

{/foreach}
</table>

{include file='footer.tpl'}
