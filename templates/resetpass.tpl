{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent mainShadow">

<div class="header">
Reset Your Password
</div>

<div id="main3">

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishresetpass">
<table class="formTable">
<tr>
<td><label for="newpassword">New password:</label></td><td><input type="password" name="newpassword"></td>
</tr>
<tr>
<td><label for="newpassword2">Confirm password:</label></td><td><input type="password" name="newpassword2"></td>
</tr>
<tr><td><input type="submit" value="Change" name="login" /></td></tr>
</table>
<input type="hidden" value="{$key}" name="key" />
</form>

</div>
</div>
</div>

{include file='footer.tpl'}
