{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent">

<div class="header">
Login
</div>

<div id="main3">

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<div>
<form method="post" action="index.php?a=finishlogin">
<table>
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

</div>
</div>
</div>

{include file='footer.tpl'}
