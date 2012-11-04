{include file='header.tpl' jsmodule='bet' header='Make A Pick' homelink=true}

<span id="poolSeason" style="display:none;">{$pool.season}</span>

{if $bets}
<div class="pickSection">
Your other picks:
<table class="betTable displayTable">
  <thead>
    <tr>
    <th>Week</th>
    <th>Team</th>
    </tr>
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
</div>
{/if}

<div class="pickSection">
Games for week {$week}:
<table class="betTable displayTable">
  <thead>
    <tr>
    <th>Teams</th>
    <th>Game time</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$games item=game}
      <tr class="{cycle values=light,dark} {if $game.start->sec < $smarty.now}gamestarted{/if}">
	{if $game.start->sec < $smarty.now}
        <td><span id="{$game.away_team._id}" title="{$game.away_team.abbreviation}" class="teamName">{$game.away_team.abbreviation}</span> {$game.away_score} @ <span id="{$game.home_team._id}" title="{$game.home_team.abbreviation}" class="teamName">{$game.home_team.abbreviation}</span> {$game.home_score}</td>
	{else}
        <td><span id="{$game.away_team._id}" title="{$game.away_team.abbreviation}" class="teamName{if !array_key_exists((string)$game.away_team._id, $teams)} teampicked{/if}">{$game.away_team.abbreviation}</span> @ <span id="{$game.home_team._id}" title="{$game.home_team.abbreviation}" class="teamName{if !array_key_exists((string)$game.home_team._id, $teams)} teampicked{/if}">{$game.home_team.abbreviation}</span></td>
	{/if}
	<td><time datetime="{$game.localstart->format('Y-m-d\TH:i:sO')}">{$game.localstart->format('D M j, Y g:i a T')}</time></td>
      </tr>
    {/foreach}
  </tbody>
</table>
</div>

<div class="pickSection">
<form action="index.php?a=addbet" method="post" id="frmBet">
<label for="betSelect">Pick for week {$week}:</label>
<select name="t" id="betSelect">
<option value="">Choose a team...</option>
{foreach from=$teams item=team}
<option value="{$team._id}">{$team.home} {$team.team}</option>
{/foreach}
</select>
<input type="hidden" name="p" value="{$pool._id}" />
<input type="hidden" name="w" value="{$week}" />
<input type="hidden" name="csrftoken" value="{$csrftoken}" />
<input type="submit" value="Pick" />
</form>
</div>

{include file='footer.tpl'}
