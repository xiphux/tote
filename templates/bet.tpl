{include file='header.tpl'}

{if $bets}
<p>
Your other bets:
<table class="betTable">
  <thead>
    <th>Week</th>
    <th>Team</th>
  </thead>
  <tbody>
    {foreach from=$bets key=wknum item=team}
      <tr class="{cycle values=light,dark}">
        <td>{$wknum}</td>
	<td>{$team.abbreviation}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
</p>
{/if}

<p>
Games for week {$week}:
<table class="betTable">
  <thead>
    <th>Teams</th>
    <th>Game time</th>
  </thead>
  <tbody>
    {foreach from=$games item=game}
      <tr class="{cycle values=light,dark} {if $game.start->sec < $smarty.now}gamestarted{/if}">
        <td>{$game.away_team.abbreviation} {$game.away_score} @ {$game.home_team.abbreviation} {$game.home_score}</td>
	<td>{$game.start->sec|date_format:"%b %e, %Y %l:%M %p"}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
</p>

<p>
<form action="index.php?a=addbet" method="get">
<label for="team">Bet on week {$week}:</label>
<select name="team">
<option value="">Choose a team...</option>
{foreach from=$teams item=team}
<option value="{$team._id}">{$team.home} {$team.team}</option>
{/foreach}
</select>
<input type="hidden" name="p" value="{$pool._id}" />
<input type="hidden" name="w" value="{$week}" />
<input type="hidden" name="a" value="addbet" />
<input type="submit" value="Bet" />
</form>
</p>

{include file='footer.tpl'}
