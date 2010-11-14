{include file='header.tpl'}

<p>Editing bets for {if $entrant.first_name}{$entrant.first_name}{if $entrant.last_name} {$entrant.last_name}{/if}{else}{$entrant.username}{/if} in {$pool.name} [{$pool.season}-{$pool.season+1}]</p>

<form action="index.php?a=savebets" method="post" />
{foreach from=$bets key=week item=bet}
<div>
<label for="week{$week}">Week {$week}:</label>
<select name="week{$week}">
<option value="">No bet</option>
{foreach from=$teams item=team}
<option value="{$team._id}" {if $team._id == $bet}selected="selected"{/if}>{$team.home} {$team.team}</option>
{/foreach}
</select>
</div>
{/foreach}
<div>
<input type="hidden" name="p" value="{$pool._id}" />
<input type="hidden" name="u" value="{$entrant._id}" />
<input type="submit" value="Save" />
</form>

{include file='footer.tpl'}
