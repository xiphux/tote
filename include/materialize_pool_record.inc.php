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
	global $db;

	if (empty($poolid))
		return;

	if (!is_numeric($poolid))
		return;

	$expirestmt = $db->prepare('SELECT MAX(start) FROM ' . TOTE_TABLE_GAMES . ' AS games WHERE season_id=(SELECT season_id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=:game_pool_id) AND week=(SELECT MIN(week) FROM ' . TOTE_TABLE_GAMES . ' AS opengames WHERE opengames.season_id=(SELECT season_id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=:week_pool_id) AND opengames.start>UTC_TIMESTAMP())');
	$expirestmt->bindParam(':game_pool_id', $poolid, PDO::PARAM_INT);
	$expirestmt->bindParam(':week_pool_id', $poolid, PDO::PARAM_INT);
	$expirestmt->execute();
	$expire = null;
	$expirestmt->bindColumn(1, $expire);
	$expirestmt->fetch(PDO::FETCH_BOUND);
	$expirestmt = null;

	$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE, ' . TOTE_TABLE_POOLS . ' WRITE, ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' READ');
	$db->exec('SET foreign_key_checks=0');
	$db->exec('SET unique_checks=0');
	$db->exec('DELETE FROM ' . TOTE_TABLE_POOL_RECORDS . ' WHERE pool_id=' . $db->quote($poolid));
	$db->exec('INSERT INTO ' . TOTE_TABLE_POOL_RECORDS . ' SELECT * FROM ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' WHERE pool_id=' . $db->quote($poolid));
	$db->exec('SET foreign_key_checks=1');
	$db->exec('SET unique_checks=1');
	$db->exec('UPDATE ' . TOTE_TABLE_POOLS . ' SET record_last_materialized=UTC_TIMESTAMP(), record_needs_materialize=0, record_next_materialize=' . ($expire === null ? 'NULL' : $db->quote($expire)) . ' WHERE id=' . $db->quote($poolid));
	$db->exec('UNLOCK TABLES');

}
