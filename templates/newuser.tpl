{include file='header.tpl' source='newuser' jsmodule='newuser' small=true header='Add A New User' homelink=true mainlink=true}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=adduser">
<table class="formTable">
  <tr>
    <td><label for="username">Username:</label></td>
    <td><input type="text" name="username" id="username" {if $username}value="{$username}"{/if} autofocus /></td>
    <td></td>
  </tr>
  <tr>
    <td><label for="firstname">First name:</label></td>
    <td><input type="text" name="firstname" id="firstname" {if $firstname}value="{$firstname}"{/if} /></td>
    <td></td>
  </tr>
  <tr>
    <td><label for="lastname">Last name:</label></td>
    <td><input type="text" name="lastname" id="lastname" {if $lastname}value="{$lastname}"{/if} /></td>
    <td></td>
  </tr>
  <tr>
    <td><label for="email">Email:</label></td>
    <td><input type="email" name="email" id="email" {if $email}value="{$email}"{/if} /></td>
    <td></td>
  </tr>
  <tr>
    <td><label for="password">Password:</label></td>
    <td><input type="password" name="password" id="password" /></td>
    <td><input type="button" id="generateButton" value="Generate random password" /></td>
  </tr>
  <tr>
    <td><label for="password2">Confirm password:</label></td>
    <td><input type="password" name="password2" id="password2" /></td>
    <td id="randomPasswordDisplay"></td>
  </tr>
  <tr>
    <td></td>
    <td><input type="submit" value="Create" /></td>
    <td></td>
  </tr>
</table>
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

{include file='footer.tpl'}
