{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=saveuser">
<table>
  <tr>
    <td><label for="username">Username:</label></td>
    <td><input type="text" name="username" {if $username}value="{$username}"{/if} /></td>
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
</form>

{include file='footer.tpl'}
