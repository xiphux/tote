{if $win}
Congratulations, you picked the {$bet.home} {$bet.team} in {$pool.name} [{$pool.season}-{$pool.season+1}] for week {$week} and they won!
{elseif $loss}
You picked the {$bet.home} {$bet.team} in {$pool.name} [{$pool.season}-{$pool.season+1}] for week {$week}, but unfortunately, they lost.  Better luck next time!
{else}
You picked the {$bet.home} {$bet.team} in {$pool.name} [{$pool.season}-{$pool.season+1}] for week {$week}, but the game tied so it's a push.
{/if}

Final score: {$awayteam.abbreviation} {$awayscore} @ {$hometeam.abbreviation} {$homescore}
