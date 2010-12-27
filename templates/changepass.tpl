{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent">

<div class="header">Change Your Password</div>

<div id="main3">

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
<td><label for="oldpassword">Old password:</label></td><td><input type="password" name="oldpassword" /></td>
</tr>
<tr><td><label for="newpassword">New password:</label></td><td><input type="password" name="newpassword"></td>
<tr><td><label for="newpassword2">Confirm password:</label></td><td><input type="password" name="newpassword2"></td>
<tr><td><input type="submit" value="Change" name="login" /></td></tr>
</table>
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

</div>
</div>
</div>

{include file='footer.tpl'}
