<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel>
  <title>{$pool.name} [{$pool.season}-{$pool.season+1}]</title>
  <link>{$self}?p={$pool._id}</link>
  <description>{$pool.name} [{$pool.season}-{$pool.season+1}] recent actions</description>
  <language>en</language>

{foreach from=$actions key=time item=timeactions}

 {foreach from=$timeactions item=action}
   <item>
     <guid isPermaLink="false">tag:{$domain},{$action.time->format('Y-m-d')}:{$pool._id}:{$time}:{$action.week}:{$action.user._id}</guid>
     <pubDate>{$action.time->format('r')}</pubDate>
     {if $action.action == 'bet'}
       <author>{$action.user.email} ({if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if})</author>
       <title>{if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet: {$action.team.abbreviation}</title>
       <description>{if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet: {$action.team.abbreviation}</description>
       <content:encoded>
         <![CDATA[
           <p>{if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} bet on the {$action.team.home} {$action.team.team} in week {$action.week}.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 'edit'}
     	<author>{$action.admin.email} ({if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if})</author>
       {if $action.from_team && $action.to_team}
       <title>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet changed from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}</title>
       <description>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet changed from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if} changed {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet from the {$action.from_team.home} {$action.from_team.team} to the {$action.to_team.home} {$action.to_team.team}.</p>
	 ]]>
       </content:encoded>
       {elseif $action.from_team}
       <title>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet {$action.from_team.abbreviation} removed</title>
       <description>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet {$action.from_team.abbreviation} removed</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if} removed {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet on the {$action.from_team.home} {$action.from_team.team}.</p>
	 ]]>
       </content:encoded>
       {elseif $action.to_team}
       <title>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet {$action.to_team.abbreviation} added</title>
       <description>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet {$action.to_team.abbreviation} added</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if} added the {$action.to_team.home} {$action.to_team.team} as {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if}'s week {$action.week} bet.</p>
	 ]]>
       </content:encoded>
       {/if}
     {elseif $action.action == 'addentrant'}
       <title>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} added to pool</title>
       <description>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} added to pool</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if} added {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} to the pool.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 'removeentrant'}
       <title>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} removed from pool</title>
       <description>Edit: {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} removed from pool</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {if $action.admin.first_name}{$action.admin.first_name}{if $action.admin.last_name} {$action.admin.last_name}{/if}{else}{$action.admin.username}{/if} removed {if $action.user.first_name}{$action.user.first_name}{if $action.user.last_name} {$action.user.last_name}{/if}{else}{$action.user.username}{/if} from the pool.</p>
	 ]]>
       </content:encoded>
     {/if}
   </item>
 {/foreach}

{/foreach}
  </channel>
</rss>
