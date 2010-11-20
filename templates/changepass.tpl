{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishchangepass">
<table>
<tr>
<td><label for="oldpassword">Old password:</label></td><td><input type="password" name="oldpassword" /></td>
</tr>
<tr><td><label for="newpassword">New password:</label></td><td><input type="password" name="newpassword"></td>
<tr><td><label for="newpassword2">Confirm password:</label></td><td><input type="password" name="newpassword2"></td>
<tr><td><input type="submit" value="Change" name="login" /></td></tr>
</table>
</form>

{include file='footer.tpl'}
