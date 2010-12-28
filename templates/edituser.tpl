{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent mainShadow">

<div class="header">
Edit A User
</div>

<div id="main3">

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=saveuser">
<table class="formTable">
  <tr>
    <td><label for="username">Username:</label></td>
    <td>{$username}</td>
  </tr>
  <tr>
    <td><label for="firstname">First name:</label></td>
    <td><input type="text" name="firstname" {if $firstname}value="{$firstname}"{/if} /></td>
  </tr>
  <tr>
    <td><label for="lastname">Last name:</label></td>
    <td><input type="text" name="lastname" {if $lastname}value="{$lastname}"{/if} /></td>
  </tr>
  <tr>
    <td><label for="email">Email:</label></td>
    <td><input type="email" name="email" {if $email}value="{$email}"{/if} /></td>
  </tr>
  <tr>
    <td></td>
    <td><input type="checkbox" name="admin" {if $admin}checked="checked"{/if} /> <label for="admin">Admin</label></td>
  </tr>
  <tr>
    <td><input type="submit" value="Save" /></td>
  </tr>
</table>
<input type="hidden" name="u" value="{$userid}" />
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

</div>
</div>
</div>

{include file='footer.tpl'}
