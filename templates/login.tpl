{include file='header.tpl' small=true header='Login' homelink=true source='login'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<div>
<form method="post" action="index.php?a=finishlogin">
<table class="formTable">
<tr>
<td>
<label for="username">Username:</label>
</td>
<td>
<input type="text" name="username" />
</td>
</tr>
<tr>
<td>
<label for="password">Password:</label>
</td>
<td>
<input type="password" name="password">
</td>
</tr>
<tr>
<td>
</td>
<td>
<input type="submit" value="Login" name="login" />
</td>
</tr>
</table>
</form>
</div>

<div>
<a href="index.php?a=recoverpass">Forgot password?</a>
</div>

{include file='footer.tpl'}
