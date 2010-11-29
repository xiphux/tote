{if $win}
Congratulations, you bet on the {$bet.home} {$bet.team} in {$pool.name} [{$pool.season}-{$pool.season+1}] for week {$week} and they won!
{elseif $loss}
You bet on the {$bet.home} {$bet.team} in {$pool.name} [{$pool.season}-{$pool.season+1}] for week {$week}, but unfortunately, they lost.  Better luck next time!
{else}
You picked the {$bet.home} {$bet.team} in {$pool.name} [{$pool.season}-{$pool.season+1}] for week {$week}, but the game tied so the bet is a push.
{/if}

Final score: {$awayteam.abbreviation} {$awayscore} @ {$hometeam.abbreviation} {$homescore}
