{include file='header.tpl' small=true header='Recover Your Password' homelink=true}

<p>Enter the email address used for the account, and we'll email instructions for setting a new password.</p>

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishrecoverpass">
<label for="email">Email:</label> <input type="email" name="email" class="initialFocus"><br />
<input type="submit" value="Recover" name="login" />
</form>

{include file='footer.tpl'}
