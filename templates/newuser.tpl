{include file='header.tpl' source='newuser'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=adduser">
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
    <td><label for="password">Password:</label></td>
    <td><input type="password" name="password" id="password" /></td>
    <td><input type="button" id="generateButton" value="Generate random password" /></td>
    <td id="randomPasswordDisplay"></td>
  </tr>
  <tr>
    <td><label for="password2">Confirm password:</label></td>
    <td><input type="password" name="password2" id="password2" /></td>
  </tr>
  <tr>
    <td><input type="submit" value="Create" /></td>
  </tr>
</table>
</form>

{include file='footer.tpl'}