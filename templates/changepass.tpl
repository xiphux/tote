{include file='header.tpl' small=true header='Change Your Password' homelink=true}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishchangepass">
<table class="formTable">
<tr>
<td><label for="oldpassword">Old password:</label></td><td><input type="password" name="oldpassword" id="oldpassword" autofocus /></td>
</tr>
<tr><td><label for="newpassword">New password:</label></td><td><input type="password" name="newpassword" id="newpassword" /></td>
<tr><td><label for="newpassword2">Confirm password:</label></td><td><input type="password" name="newpassword2" id="newpassword2" /></td>
<tr><td></td><td><input type="submit" value="Change" /></td></tr>
</table>
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

{include file='footer.tpl'}
