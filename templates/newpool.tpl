{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent mainShadow">

<div class="header">
Add A New Pool
</div>

<div id="main3">

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=addpool">
<table class="formTable">
  <tr>
    <td><label for="name">Pool name:</label></td>
    <td><input type="text" name="name" {if $name}value="{$name}"{/if} /></td>
  </tr>
  <tr>
    <td><label for="season">Season:</label></td>
    <td><input type="text" name="season" value="{if $season}{$season}{else}{$smarty.now|date_format:"%Y"}{/if}" /></td>
  </tr>
  <tr>
    <td><label for="fee">Fee:</label></td>
    <td>$<input type="text" name="fee" value="{if $fee}{$fee}{else}0.00{/if}" /></td>
  </tr>
  <tr>
    <td><input type="submit" value="Create" /></td>
  </tr>
</table>
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

</div>
</div>
</div>

{include file='footer.tpl'}
