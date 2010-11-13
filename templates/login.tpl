{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishlogin">
<label for="username">Username:</label> <input type="text" name="username" /><br />
<label for="password">Password:</label> <input type="password" name="password"><br />
<input type="submit" value="Login" name="login" />
</form>

{include file='footer.tpl'}
