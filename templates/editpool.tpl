{include file='header.tpl' jsmodule='editpool' header='Manage Your Pool' homelink=true}

<span style="display: none" id="poolID">{$pool._id}</span>

<div class="poolEditToolbar">
<div class="left">
</div>
<div class="right">
<form action="{$SCRIPT_NAME}?a=setpoolname" method="post">
<label for="poolName">Pool name:</label> <input type="text" name="poolname" id="poolName" value="{$pool.name}" /><input type="submit" value="Set" />
<input type="hidden" name="p" value="{$pool._id}" />
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>
</div>
<div class="clear">
</div>
</div>

<div class="userEditDiv">
Available users:
 <div class="availableUsers">
  {foreach from=$availableusers item=availableuser}
  <div id="{$availableuser._id}" class="userListItem">
    <table>
      <tr>
        <td class="checkbox"><input type="checkbox" class="selectuser" /></td>
	<td class="userinfo" colspan="2">
    <span class="username">{if $availableuser.first_name}{$availableuser.first_name}{if $availableuser.last_name} {$availableuser.last_name}{/if}{else}{$availableuser.username}{/if}</span>
    {if $availableuser.email}<br />{$availableuser.email}{/if}
    </td>
    </tr>
    <tr class="poolAdmin" style="display:none;">
      <td>
      </td>
      <td><input type="checkbox" class="primaryadmin admincheckbox"><label>Pool administrator</label></td>
      <td><input type="checkbox" class="secondaryadmin admincheckbox"><label>Non-voting administrator</label></td>
    </tr>
    </table>
  </div>
  {/foreach}
 </div>
</div>

<div class="movementDiv">
<div>
<input id="addButton" type="button" value="Add to pool &gt;" disabled="disabled" /><br />
<input id="removeButton" type="button" value="&lt; Remove from pool" disabled="disabled" /><br />
<img src="images/editpool-loader.gif" id="editSpinner" style="display: none;" alt="Loading..." />
</div>
</div>

<div class="poolEditDiv">
Users in pool:
<div class="poolUsers">
  {foreach from=$poolusers item=pooluser}
  <div id="{$pooluser._id}" class="userListItem">
    <table>
      <tr>
        <td class="checkbox"><input type="checkbox" class="selectuser" /></td>
	<td class="userinfo">
    <span class="username">{if $pooluser.first_name}{$pooluser.first_name}{if $pooluser.last_name} {$pooluser.last_name}{/if}{else}{$pooluser.username}{/if}</span>
    {if $pooluser.email}<br />{$pooluser.email}{/if}
    </td>
    <td class="alert">{if $pooluser.hasbets}<span title="Removing this user from the pool will discard all of his/her picks">User has active picks</span>{/if}</td>
    </tr>
    <tr class="poolAdmin">
      <td>
      </td>
      <td><input type="checkbox" class="primaryadmin admincheckbox" {if $pooluser.primaryadmin}checked="checked"{/if}><label>Pool administrator</label></td>
      <td><input type="checkbox" class="secondaryadmin admincheckbox" {if $pooluser.secondaryadmin}checked="checked"{/if}><label>Non-voting administrator</label></td>
    </tr>
    </table>
  </div>
  {/foreach}
</div>

</div>

<div class="clear">
</div>

<div class="poolActionsDiv">
<a href="{$SCRIPT_NAME}?a=deletepool&amp;p={$pool._id}&amp;csrftoken={$csrftoken}" class="alert deletePoolAction">Delete pool</a>
</div>

{include file='footer.tpl'}
