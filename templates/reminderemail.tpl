Hello {if $user.first_name}{$user.first_name}{if $user.last_name} {$user.last_name}{/if}{else}{$user.username}{/if},

This is your friendly reminder from {$sitename} that week {$week} of the {$year} NFL season is about to start.  If you're in a pool, don't forget to make a pick!

Upcoming games for week {$week}:
{foreach from=$games item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}

{$day}:
{assign var=lastday value=$day}{/if}
{$game.away_team.abbreviation} @ {$game.home_team.abbreviation} 		at {$game.localstart->format('g:i a T')}
{/foreach}
