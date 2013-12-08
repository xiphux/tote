Hello {$user.user_display_name},

This is your friendly reminder from {$sitename} that week {$week} of the {$year} NFL season is about to start.  Don't forget to make a pick!

Upcoming games for week {$week}:
{foreach from=$games item=game}
{assign var=day value=$game.localstart->format('D M j, Y')}{if $day != $lastday}

{$day}:
{assign var=lastday value=$day}{/if}
{$game.away_team_abbr} @ {$game.home_team_abbr} 		at {$game.localstart->format('g:i a T')}
{/foreach}
