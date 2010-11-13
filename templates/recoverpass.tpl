{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<p>Enter the email address used for the account, and we'll email instructions for setting a new password.</p>

<form method="post" action="index.php?a=finishrecoverpass">
<label for="email">Email:</label> <input type="email" name="email"><br />
<input type="submit" value="Recover" name="login" />
</form>

{include file='footer.tpl'}
