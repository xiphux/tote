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
<option value="{$tz}" {if ($tz == $user.timezone) || (!$user.timezone && ($tz == $defaulttimezone))}selected="selected"{/if}>{$tz|replace:'_':' '}</option>
{/foreach}
</select>
</div>

{if $enablereminders}
<br />
<div>
<input type="checkbox" name="reminder" value="1" {if $user.reminder}checked="checked"{/if} /><label for="reminder">Email me a reminder before the first game of the week</label><br />
<label for="remindertime">How many hours beforehand:</label><input type="text" name="remindertime" value="{if $user.remindertime}{$user.remindertime}{else}{$defaultremindertime}{/if}" />
</div>
{/if}

<br />
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
<div>
<input type="submit" value="Save" />
</div>

</form>

{include file='footer.tpl'}
