<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
  <title>{$pool.name} [{$pool.season}-{$pool.season+1}]</title>
  <link>{$self}?p={$pool.id}</link>
  <atom:link href="{$self}?a=rss&amp;p={$pool.id}" rel="self" type="application/rss+xml" />
  <description>{$pool.name} [{$pool.season}-{$pool.season+1}] recent actions</description>
  <language>en</language>

{foreach from=$actions item=action}
   <item>
     <guid isPermaLink="false">tag:{$domain},{$action.time->format('Y-m-d')}:{$pool.id}:{$action.time->format('U')}:{$action.week}:{$action.user_id}</guid>
     <pubDate>{$action.time->format('r')}</pubDate>
     {if $action.action == 4}
       <author>{$action.user_email} ({$action.username})</author>
       <title>{$action.username}'s week {$action.week} pick: {$action.team_abbr}</title>
       <description>{$action.username}'s week {$action.week} pick: {$action.team_abbr}</description>
       <content:encoded>
         <![CDATA[
           <p>{$action.username} picked the {$action.team_name} in week {$action.week}.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 5}
     	<author>{$action.admin_email} ({$action.admin_username})</author>
       {if $action.old_team_id && $action.team_id}
       <title>Edit: {$action.username}'s week {$action.week} pick changed from {$action.old_team_abbr} to {$action.team_abbr}</title>
       <description>Edit: {$action.username}'s week {$action.week} pick changed from {$action.old_team_abbr} to {$action.team_abbr}</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_username} changed {$action.username}'s week {$action.week} pick from the {$action.old_team_name} to the {$action.team_name}.</p>
	 ]]>
       </content:encoded>
       {elseif $action.old_team_id}
       <title>Edit: {$action.username}'s week {$action.week} pick {$action.old_team_abbr} removed</title>
       <description>Edit: {$action.username}'s week {$action.week} pick {$action.old_team_abbr} removed</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_username} removed {$action.username}'s week {$action.week} pick of the {$action.old_team_name}.</p>
	 ]]>
       </content:encoded>
       {elseif $action.team_id}
       <title>Edit: {$action.username}'s week {$action.week} pick {$action.team_abbr} added</title>
       <description>Edit: {$action.username}'s week {$action.week} pick {$action.team_abbr} added</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_username} added the {$action.team_name} as {$action.username}'s week {$action.week} pick.</p>
	 ]]>
       </content:encoded>
       {/if}
     {elseif $action.action == 1}
       <title>Edit: {$action.username} added to pool</title>
       <description>Edit: {$action.username} added to pool</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_username} added {$action.username} to the pool.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 2}
       <title>Edit: {$action.username} removed from pool</title>
       <description>Edit: {$action.username} removed from pool</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_username} removed {$action.username} from the pool.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 5}
	<author>{$action.admin_email} ({$action.admin_username})</author>
       {if $action.newpooladmin == 2}
         {if $action.oldpooladmin == 1}
	       <title>Edit: {$action.username} changed from pool administrator to non-voting pool administrator</title>
	       <description>Edit: {$action.username} changed from pool administrator to non-voting pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_username} changed {$action.username} from pool administrator to non-voting pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.username} set as non-voting pool administrator</title>
	       <description>Edit: {$action.username} set as non-voting pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_username} set {$action.username} as a non-voting pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {/if}
       {elseif $action.newpooladmin == 1}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.username} changed from non-voting pool administrator to pool administrator</title>
	       <description>Edit: {$action.username} changed from non-voting pool administrator to pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_username} changed {$action.username} from non-voting pool administrator to pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.username} set as pool administrator</title>
	       <description>Edit: {$action.username} set as pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_username} set {$action.username} as a pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {/if}
       {elseif $action.newpooladmin == 0}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.username} removed from non-voting pool administrators</title>
	       <description>Edit: {$action.username} removed from non-voting pool administrators</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_username} removed {$action.username} from the non-voting pool administrators.</p>
		 ]]>
	       </content:encoded>
	 {elseif $action.oldpooladmin == 1}
	       <title>Edit: {$action.username} removed from pool administrators</title>
	       <description>Edit: {$action.username} removed from pool administrators</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_username} removed {$action.username} from the pool administrators.</p>
		 ]]>
	       </content:encoded>
	 {/if}
       {/if}
     {/if}
   </item>
{/foreach}
  </channel>
</rss>
