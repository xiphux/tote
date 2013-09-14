{if $win}
Congratulations, you picked the {$data.pick_team} in {$data.pool_name} [{$data.season}-{$data.season+1}] for week {$week} and they won!
{elseif $loss}
You picked the {$data.pick_team} in {$data.pool_name} [{$data.season}-{$data.season+1}] for week {$week}, but unfortunately, they lost. Better luck next time!
{else}
You picked the {$data.pick_team} in {$data.pool_name} [{$data.season}-{$data.season+1}] for week {$week}, but the game tied so it's a push.
{/if}

Final score: {$data.away_team_abbr} {$awayscore} @ {$data.home_team_abbr} {$homescore}
