{include file='header.tpl'}

<div id="main">
<div id="main2" class="mainShadow">
<div class="header">
Edit A User's Bets
</div>

<div id="main3">
<p>Editing bets for {if $entrant.first_name}{$entrant.first_name}{if $entrant.last_name} {$entrant.last_name}{/if}{else}{$entrant.username}{/if} in {$pool.name} [{$pool.season}-{$pool.season+1}]</p>

<form action="index.php?a=savebets" method="post" />
<table class="formTable">
{foreach from=$bets key=week item=bet}
<tr>
<td><label for="week[{$week}]">Week {$week}:</label></td>
<td>
<select name="week[{$week}]">
<option value="">No bet</option>
{foreach from=$teams item=team}
<option value="{$team._id}" {if $team._id == $bet}selected="selected"{/if}>{$team.home} {$team.team}</option>
{/foreach}
</select>
</td>
</tr>
{/foreach}
<tr>
<td></td>
<td>
<input type="submit" value="Save" />
</td>
</tr>
</table>
<div>
<input type="hidden" name="p" value="{$pool._id}" />
<input type="hidden" name="u" value="{$entrant._id}" />
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
</form>

</div>
</div>
</div>

{include file='footer.tpl'}
