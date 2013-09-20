<?php

/*
 * Views powering this.  The record tabulation view is expensive,
 * but makes pool record tabulation and load extraordinarily cheap and fast
 * when materialized into an indexed static table with the same structure
 * as the view
 *
 * season_weeks_view
 *  Get a list of each week, by season
 *  Necessary since aliased subqueries can't be used in views
    SELECT DISTINCT season_id, week FROM games
 *
 * pool_records_view
 *  Get each entrant for each pool, and their pick (or lack of pick) for each week
 *  as well as the picked game and the result (win/loss/tie/spread) of each week
    SELECT
    pools.id AS pool_id,
    pool_entries.user_id AS user_id,
    season_weeks_view.week AS week,
    pool_entry_picks.team_id AS team_id,
    games.id AS game_id,
    (
      pool_entry_picks.team_id IS NOT NULL
        AND games.home_score IS NOT NULL
        AND games.away_score IS NOT NULL
        AND (
          (
             pool_entry_picks.team_id=games.home_team_id
             AND games.home_score>games.away_score
          )
          OR
          (
             pool_entry_picks.team_id=games.away_team_id
             AND games.away_score>games.home_score
          )
        )
    ) AS win,
    (
      pool_entry_picks.team_id IS NOT NULL
        AND games.home_score IS NOT NULL
        AND games.away_score IS NOT NULL
        AND (
          (
            pool_entry_picks.team_id=games.home_team_id
              AND games.home_score<games.away_score
          )
          OR
          (
            pool_entry_picks.team_id=games.away_team_id
              AND games.away_score<games.home_score
          )
        )
    )
    OR
    (
      pool_entry_picks.team_id IS NULL
        AND season_weeks_view.week NOT IN (
          SELECT DISTINCT
          week
          FROM games AS opengames
          WHERE opengames.season_id=pools.season_id
            AND opengames.start>UTC_TIMESTAMP()
        )
    ) AS loss,
    (
      pool_entry_picks.team_id IS NOT NULL
        AND games.home_score IS NOT NULL
        AND games.away_score IS NOT NULL
        AND games.home_score=games.away_score
    ) AS tie,
    (CASE
       WHEN
            pool_entry_picks.team_id IS NOT NULL
              AND games.home_score IS NOT NULL
	      AND games.away_score IS NOT NULL
	      AND pool_entry_picks.team_id=games.home_team_id
         THEN
              CAST(games.home_score AS SIGNED INT)-CAST(games.away_score AS SIGNED INT)
       WHEN
            pool_entry_picks.team_id IS NOT NULL
	      AND games.home_score IS NOT NULL
	      AND games.away_score IS NOT NULL
	      AND pool_entry_picks.team_id=games.away_team_id
         THEN
              CAST(games.away_score AS SIGNED INT)-CAST(games.home_score AS SIGNED INT)
       WHEN
            pool_entry_picks.team_id IS NULL
	      AND season_weeks_view.week>(
	                                   (
					     SELECT MAX(spreadgames.week)
					     FROM games AS spreadgames
					     WHERE spreadgames.season_id=pools.season_id
					   )-4
				         )
              AND season_weeks_view.week NOT IN (
	                                          SELECT DISTINCT
						  week
						  FROM games AS opengames
						  WHERE opengames.season_id=pools.season_id
						    AND opengames.start>UTC_TIMESTAMP()
					        )
         THEN
	      -10
       ELSE
            NULL
     END) AS spread,
     COALESCE(
       (
         season_weeks_view.week>=(
	                           SELECT MIN(week)
				   FROM games AS opengames
				   WHERE opengames.season_id=pools.season_id
				   AND opengames.start>UTC_TIMESTAMP()
				 )
       ),
       0
     ) AS open
     FROM pools
     CROSS JOIN season_weeks_view
       ON pools.season_id=season_weeks_view.season_id
     CROSS JOIN pool_entries
       ON pool_entries.pool_id=pools.id
     LEFT JOIN pool_entry_picks
       ON pool_entries.id=pool_entry_picks.pool_entry_id
       AND season_weeks_view.week=pool_entry_picks.week
     LEFT JOIN games
       ON games.season_id=season_weeks_view.season_id
       AND games.week=season_weeks_view.week
       AND (
            pool_entry_picks.team_id=games.away_team_id
	    OR pool_entry_picks.team_id=games.home_team_id
	   )
 *
 */

function materialize_pool_record($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return;

	if (!is_numeric($poolid))
		return;

	$expirestmt = $mysqldb->prepare('SELECT MAX(start) FROM ' . TOTE_TABLE_GAMES . ' AS games WHERE season_id=(SELECT season_id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?) AND week=(SELECT MIN(week) FROM ' . TOTE_TABLE_GAMES . ' AS opengames WHERE opengames.season_id=(SELECT season_id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?) AND opengames.start>UTC_TIMESTAMP())');
	$expirestmt->bind_param('ii', $poolid, $poolid);
	$expire = null;
	$expirestmt->bind_result($expire);
	$expirestmt->execute();
	$expirestmt->fetch();
	$expirestmt->close();

	$materializequery = <<<EOQ
LOCK TABLES %s WRITE, %s WRITE, %s READ;
DELETE FROM %s WHERE pool_id=%d;
INSERT INTO %s SELECT * FROM %s WHERE pool_id=%d;
UPDATE %s SET record_last_materialized=UTC_TIMESTAMP(), record_needs_materialize=0, record_next_materialize=%s WHERE id=%d;
UNLOCK TABLES;
EOQ;

	$materializequery = sprintf($materializequery, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOLS, TOTE_TABLE_POOL_RECORDS_VIEW, TOTE_TABLE_POOL_RECORDS, $poolid, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, $poolid, TOTE_TABLE_POOLS, $expire === null ? 'NULL' : ("'" . $expire . "'"), $poolid);
	$mysqldb->multi_query($materializequery);
	$materializeresult = $mysqldb->store_result();
	do {
		if ($res = $mysqldb->store_result()) {
			$res->close();
		}
	} while ($mysqldb->more_results() && $mysqldb->next_result());

}
