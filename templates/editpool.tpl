{include file='header.tpl'}

<div>
<a href="{$SCRIPT_NAME}?p={$pool._id}">Back</a>
</div>

<div class="userEditDiv">
 <div class="availableUsers">
  {foreach from=$availableusers item=availableuser}
  <div id="{$availableuser._id}" class="userListItem">
    <table>
      <tr>
        <td><input type="checkbox" /></td>
	<td>
    {if $availableuser.first_name}{$availableuser.first_name}{if $availableuser.last_name} {$availableuser.last_name}{/if}{else}{$availableuser.username}{/if}<br />
    {if $availableuser.email}{$availableuser.email}{/if}
    </td>
    </tr>
    </table>
  </div>
  {/foreach}
 </div>
 <div><a href="{$SCRIPT_NAME}?a=newuser">Add a new user</a>
 </div>
</div>

<div class="poolEditDiv">

<div class="poolUsers">
  {foreach from=$poolusers item=pooluser}
  <div id="{$pooluser._id}" class="userListItem">
    <table>
      <tr>
        <td><input type="checkbox" /></td>
	<td>
    {if $pooluser.first_name}{$pooluser.first_name}{if $pooluser.last_name} {$pooluser.last_name}{/if}{else}{$pooluser.username}{/if}<br />
    {if $pooluser.email}{$pooluser.email}<br />{/if}
    {if $pooluser.hasbets}<span class="alert">User has active bets</span>{/if}
    </td>
    </tr>
    </table>
  </div>
  {/foreach}
</div>

</div>

<div class="clear">
</div>

{include file='footer.tpl'}
