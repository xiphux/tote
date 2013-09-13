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
{foreach from=$actions item=action}
<tr class="{cycle values=light,dark}">
  <td><time datetime="{$action.timelocal->format('Y-m-d\TH:i:sO')}">{$action.timelocal->format('r')}</time></td>
  <td>{$action.username}</td>
  <td>{if $action.admin_username}{$action.admin_username}{/if}</td>
  <td class="center">{if $action.week && ($action.week > 0)}{$action.week}{/if}</td>
  <td>
  {if $action.action == 4}
  Pick added: {$action.team_abbr}
  {elseif $action.action == 5}
    {if $action.team_id && $action.old_team_id}
    Admin changed pick from {$action.old_team_abbr} to {$action.team_abbr}
    {elseif $action.old_team_id}
    Admin deleted pick of {$action.old_team_abbr}
    {elseif $action.team_id}
    Admin added pick of {$action.team_abbr}
    {/if}
  {elseif $action.action == 1}
    Admin added user to pool
  {elseif $action.action == 2}
    Admin removed user from pool
  {elseif $action.action == 3}
    {if $action.admin_type == 2}
      {if $action.old_admin_type == 1}
        Admin changed user from pool administrator to non-voting pool administrator
      {elseif $action.old_admin_type == 0}
      	Admin set user as non-voting pool administrator
      {/if}
    {elseif $action.admin_type == 1}
      {if $action.old_admin_type == 2}
      	Admin changed user from non-voting pool administrator to pool administrator
      {elseif $action.old_admin_type == 0}
        Admin set user as pool administrator
      {/if}
    {elseif $action.admin_type == 0}
      {if $action.old_admin_type == 2}
      	Admin removed user from non-voting pool administrators
      {elseif $action.old_admin_type == 1}
        Admin removed user from pool administrators
      {/if}
    {/if}
  {/if}
  </td>
  <td>{if $action.comment}{$action.comment}{/if}</td>
</tr>
{/foreach}
</table>

{if !$js}
{include file='footer.tpl'}
{/if}
