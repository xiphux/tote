{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishchangepass">
<label for="oldpassword">Old password:</label> <input type="password" name="oldpassword" /><br />
<label for="newpassword">New password:</label> <input type="password" name="newpassword"><br />
<label for="newpassword2">Confirm password:</label> <input type="password" name="newpassword2"><br />
<input type="submit" value="Change" name="login" />
</form>

{include file='footer.tpl'}
