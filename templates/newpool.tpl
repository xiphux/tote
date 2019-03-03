{include file='header.tpl' small=true header='Add A New Pool' homelink=true}

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
    <td><input type="text" name="name" id="name" {if $name}value="{$name}"{/if} autofocus /></td>
  </tr>
  <tr>
    <td><label for="season">Season:</label></td>
    <td>
    <select name="season" id="season">
    {foreach from=$seasons item=eachseason}
    <option value="{$eachseason}" {if $season == $eachseason}selected="selected"{/if}>{$eachseason}-{$eachseason+1}</option>
    {/foreach}
    </select>
    </td>
  </tr>
  <tr>
    <td><label for="fee">Fee:</label></td>
    <td>$<input type="text" name="fee" id="fee" value="0.00" /></td>
  </tr>
  <tr>
    <td></td>
    <td><input type="submit" value="Create" /></td>
  </tr>
</table>
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

{include file='footer.tpl'}
