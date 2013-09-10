{include file='header.tpl' header="Edit A User's Picks" homelink=true}

<p>Editing picks for {if $entrant.first_name}{$entrant.first_name}{if $entrant.last_name} {$entrant.last_name}{/if}{else}{$entrant.username}{/if} in {$pool.name} [{$pool.season}-{$pool.season+1}]</p>

<form action="index.php?a=savebets" method="post">
<table class="formTable">
{foreach from=$bets key=week item=bet}
<tr>
<td><label for="week{$week}">Week {$week}:</label></td>
<td>
<select name="week[{$week}]" id="week{$week}">
<option value="">No pick</option>
{foreach from=$teams item=team}
<option value="{$team.id}" {if $team.id == $bet}selected="selected"{/if}>{$team.home} {$team.team}</option>
{/foreach}
</select>
</td>
</tr>
{/foreach}
<tr>
<td>
<label for="comment">Comment:</label>
</td>
<td>
<textarea name="comment" id="comment" rows="3" cols="20">
</textarea>
</td>
</tr>
<tr>
<td></td>
<td>
<input type="submit" value="Save" />
</td>
</tr>
</table>
<input type="hidden" name="p" value="{$pool.id}" />
<input type="hidden" name="u" value="{$entrant.id}" />
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

{include file='footer.tpl'}
