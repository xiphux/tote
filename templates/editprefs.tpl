{include file='header.tpl'}

{if $errors}
<ul>
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<form method="post" action="index.php?a=saveprefs">

<div>
<label for="timezone">Timezone:</label>
<select name="timezone">
{foreach from=$availabletimezones item=tz}
<option value="{$tz}" {if ($tz == $usertimezone) || (!$usertimezone && ($tz == $defaulttimezone))}selected="selected"{/if}>{$tz|replace:'_':' '}</option>
{/foreach}
</select>
</div>

<div>
<input type="submit" value="Save" />
</div>

</form>

{include file='footer.tpl'}
