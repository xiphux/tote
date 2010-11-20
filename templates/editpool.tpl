{include file='header.tpl' source='editpool'}

<div>
<a href="{$SCRIPT_NAME}?p={$pool._id}">Back</a>
</div>

<span style="display: none" id="poolID">{$pool._id}</span>

<div class="userEditDiv">
Available users:
 <div class="availableUsers">
  {foreach from=$availableusers item=availableuser}
  <div id="{$availableuser._id}" class="userListItem">
    <table>
      <tr>
        <td class="checkbox"><input type="checkbox" /></td>
	<td>
    <span class="username">{if $availableuser.first_name}{$availableuser.first_name}{if $availableuser.last_name} {$availableuser.last_name}{/if}{else}{$availableuser.username}{/if}</span><br />
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

<div class="movementDiv">
<div>
<input id="addButton" type="button" value="Add to pool &gt;" disabled="disabled" /><br />
<input id="removeButton" type="button" value="&lt; Remove from pool" disabled="disabled" /><br />
<img src="images/editpool-loader.gif" id="editSpinner" style="display: none;" />
</div>
</div>

<div class="poolEditDiv">
Users in pool:
<div class="poolUsers">
  {foreach from=$poolusers item=pooluser}
  <div id="{$pooluser._id}" class="userListItem">
    <table>
      <tr>
        <td class="checkbox"><input type="checkbox" /></td>
	<td>
    <span class="username">{if $pooluser.first_name}{$pooluser.first_name}{if $pooluser.last_name} {$pooluser.last_name}{/if}{else}{$pooluser.username}{/if}</span><br />
    {if $pooluser.email}{$pooluser.email}{/if}
    </td>
    {if $pooluser.hasbets}<td class="alert">User has active bets</td>{/if}
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
