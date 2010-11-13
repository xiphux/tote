{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishresetpass">
<label for="newpassword">New password:</label> <input type="password" name="newpassword"><br />
<label for="newpassword2">Confirm password:</label> <input type="password" name="newpassword2"><br />
<input type="submit" value="Change" name="login" />
<input type="hidden" value="{$key}" name="key" />
</form>

{include file='footer.tpl'}
