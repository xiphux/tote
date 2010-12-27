{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent">

<div class="header">
Recover Your Password
</div>

<div id="main3">

<p>Enter the email address used for the account, and we'll email instructions for setting a new password.</p>

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=finishrecoverpass">
<label for="email">Email:</label> <input type="email" name="email"><br />
<input type="submit" value="Recover" name="login" />
</form>

</div>
</div>
</div>

{include file='footer.tpl'}
