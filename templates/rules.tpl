{if !$js}
{include file='header.tpl'}
{/if}

<div>

<ul>

<li>Pick one team each week.</li>

<li>You can only pick each team once for the entire season.  Once a team has been picked, the website will not allow you to pick that team again.</li>

<li>A pick for a game must be submitted before the scheduled start of that game.  The website will not allow you to pick a game that has already started (according to the webserver's clock).</li>

<li>Failing to make a pick will result in a loss for that week.  Failing to make a pick during the last four weeks of the season will result in a 10 point penalty in addition to the loss.</li>

{if $email}
<li>In the event of a technical difficulty, send an email to <a href="mailto:{$email}">{$email}</a> as soon as possible.</li>
{/if}

<li>You will be entered in the pool as soon as your entry fee is received.  If your payment is late, any weeks that have already passed at the time of entry will be counted as losses.</li>

<li>Placeholder for administrator voting board rule</li>

<li>At the end of the season, the person with the best record wins.  The point spread will be used as a tiebreaker.</li>

<li>Payout: 1st place wins 75%, 2nd place wins 15%, 3rd place wins 10%.  In the event of a tie, the tied players will split the sum of their payouts.</li>

</ul>

</div>

{if !$js}
{include file='footer.tpl'}
{/if}
