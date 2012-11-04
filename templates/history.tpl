{if !$js}
{include file='header.tpl' poolinfo=$pool header='Pool History' homelink=true}

<div>
{$pool.name} [{$pool.season}-{$pool.season+1}] History
</div>
{/if}

<table class="historyTable displayTable">
<thead>
<tr>
<th>Timestamp</th>
<th>User</th>
<th>Admin</th>
<th>Week</th>
<th>Event</th>
<th>Comment</th>
</tr>
</thead>
{foreach from=$actions key=time item=timeactions}

{foreach from=$timeactions item=action}
<tr class="{cycle values=light,dark}">
  <td><time datetime="{$action.time->format('Y-m-d\TH:i:sO')}">{$action.time->format('r')}</time></td>
  <td>{$action.user_name}</td>
  <td>{if $action.admin_name}{$action.admin_name}{/if}</td>
  <td class="center">{if $action.week && ($action.week > 0)}{$action.week}{/if}</td>
  <td>
  {if $action.action == 'bet'}
  Pick added: {$action.team.abbreviation}
  {elseif $action.action == 'edit'}
    {if $action.from_team && $action.to_team}
    Admin changed pick from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}
    {elseif $action.from_team}
    Admin deleted pick of {$action.from_team.abbreviation}
    {elseif $action.to_team}
    Admin added pick of {$action.to_team.abbreviation}
    {/if}
  {elseif $action.action == 'addentrant'}
    Admin added user to pool
  {elseif $action.action == 'removeentrant'}
    Admin removed user from pool
  {elseif $action.action == 'pooladminchange'}
    {if $action.newpooladmin == 2}
      {if $action.oldpooladmin == 1}
        Admin changed user from pool administrator to non-voting pool administrator
      {elseif $action.oldpooladmin == 0}
      	Admin set user as non-voting pool administrator
      {/if}
    {elseif $action.newpooladmin == 1}
      {if $action.oldpooladmin == 2}
      	Admin changed user from non-voting pool administrator to pool administrator
      {elseif $action.oldpooladmin == 0}
        Admin set user as pool administrator
      {/if}
    {elseif $action.newpooladmin == 0}
      {if $action.oldpooladmin == 2}
      	Admin removed user from non-voting pool administrators
      {elseif $action.oldpooladmin == 1}
        Admin removed user from pool administrators
      {/if}
    {/if}
  {/if}
  </td>
  <td>{if $action.comment}{$action.comment}{/if}</td>
</tr>
{/foreach}

{/foreach}
</table>

{if !$js}
{include file='footer.tpl'}
{/if}
