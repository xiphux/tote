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
       <author>{$action.user.email} ({$action.user_name})</author>
       <title>{$action.user_name}'s week {$action.week} pick: {$action.team.abbreviation}</title>
       <description>{$action.user_name}'s week {$action.week} pick: {$action.team.abbreviation}</description>
       <content:encoded>
         <![CDATA[
           <p>{$action.user_name} picked the {$action.team.home} {$action.team.team} in week {$action.week}.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 'edit'}
     	<author>{$action.admin.email} ({$action.admin_name})</author>
       {if $action.from_team && $action.to_team}
       <title>Edit: {$action.user_name}'s week {$action.week} pick changed from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}</title>
       <description>Edit: {$action.user_name}'s week {$action.week} pick changed from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_name} changed {$action.user_name}'s week {$action.week} pick from the {$action.from_team.home} {$action.from_team.team} to the {$action.to_team.home} {$action.to_team.team}.</p>
	 ]]>
       </content:encoded>
       {elseif $action.from_team}
       <title>Edit: {$action.user_name}'s week {$action.week} pick {$action.from_team.abbreviation} removed</title>
       <description>Edit: {$action.user_name}'s week {$action.week} pick {$action.from_team.abbreviation} removed</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_name} removed {$action.user_name}'s week {$action.week} pick of the {$action.from_team.home} {$action.from_team.team}.</p>
	 ]]>
       </content:encoded>
       {elseif $action.to_team}
       <title>Edit: {$action.user_name}'s week {$action.week} pick {$action.to_team.abbreviation} added</title>
       <description>Edit: {$action.user_name}'s week {$action.week} pick {$action.to_team.abbreviation} added</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_name} added the {$action.to_team.home} {$action.to_team.team} as {$action.user_name}'s week {$action.week} pick.</p>
	 ]]>
       </content:encoded>
       {/if}
     {elseif $action.action == 'addentrant'}
       <title>Edit: {$action.user_name} added to pool</title>
       <description>Edit: {$action.user_name} added to pool</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_name} added {$action.user_name} to the pool.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 'removeentrant'}
       <title>Edit: {$action.user_name} removed from pool</title>
       <description>Edit: {$action.user_name} removed from pool</description>
       <content:encoded>
         <![CDATA[
	   <p>Admin {$action.admin_name} removed {$action.user_name} from the pool.</p>
	 ]]>
       </content:encoded>
     {elseif $action.action == 'pooladminchange'}
	<author>{$action.admin.email} ({$action.admin_name})</author>
       {if $action.newpooladmin == 2}
         {if $action.oldpooladmin == 1}
	       <title>Edit: {$action.user_name} changed from pool administrator to non-voting pool administrator</title>
	       <description>Edit: {$action.user_name} changed from pool administrator to non-voting pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_name} changed {$action.user_name} from pool administrator to non-voting pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.user_name} set as non-voting pool administrator</title>
	       <description>Edit: {$action.user_name} set as non-voting pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_name} set {$action.user_name} as a non-voting pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {/if}
       {elseif $action.newpooladmin == 1}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.user_name} changed from non-voting pool administrator to pool administrator</title>
	       <description>Edit: {$action.user_name} changed from non-voting pool administrator to pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_name} changed {$action.user_name} from non-voting pool administrator to pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.user_name} set as pool administrator</title>
	       <description>Edit: {$action.user_name} set as pool administrator</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_name} set {$action.user_name} as a pool administrator.</p>
		 ]]>
	       </content:encoded>
	 {/if}
       {elseif $action.newpooladmin == 0}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.user_name} removed from non-voting pool administrators</title>
	       <description>Edit: {$action.user_name} removed from non-voting pool administrators</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_name} removed {$action.user_name} from the non-voting pool administrators.</p>
		 ]]>
	       </content:encoded>
	 {elseif $action.oldpooladmin == 1}
	       <title>Edit: {$action.user_name} removed from pool administrators</title>
	       <description>Edit: {$action.user_name} removed from pool administrators</description>
	       <content:encoded>
		 <![CDATA[
		   <p>Admin {$action.admin_name} removed {$action.user_name} from the pool administrators.</p>
		 ]]>
	       </content:encoded>
	 {/if}
       {/if}
     {/if}
   </item>
 {/foreach}

{/foreach}
  </channel>
</rss>
