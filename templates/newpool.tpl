{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=addpool">
<table>
  <tr>
    <td><label for="name">Pool name:</label></td>
    <td><input type="text" name="name" {if $name}value="{$name}"{/if} /></td>
  </tr>
  <tr>
    <td><label for="season">Season:</label></td>
    <td><input type="text" name="season" value="{if $season}{$season}{else}{$smarty.now|date_format:"%Y"}{/if}" /></td>
  </tr>
  <tr>
    <td><input type="submit" value="Create" /></td>
  </tr>
</table>
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

{include file='footer.tpl'}