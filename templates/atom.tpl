<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="en">
  <title>{$pool.name} [{$pool.season}-{$pool.season+1}]</title>
  <subtitle type="text">{$pool.name} [{$pool.season}-{$pool.season+1}] recent actions</subtitle>
  <link href="{$self}?p={$pool._id}" />
  <link rel="self" href="{$self}?a=atom&amp;p={$pool._id}" />
  <id>{$self}?p={$pool._id}</id>
  {if $updated}
  <updated>{$updated->format('c')}</updated>
  {/if}

{foreach from=$actions key=time item=timeactions}

 {foreach from=$timeactions item=action}
   <entry>
     <id>tag:{$domain},{$action.time->format('Y-m-d')}:{$pool._id}:{$time}:{$action.week}:{$action.user_name}</id>
     <published>{$action.time->format('c')}</published>
     <updated>{$action.time->format('c')}</updated>
     {if $action.action == 'bet'}
       <author>
         <name>{$action.user_name}</name>
	 {if $action.user.email}<email>{$action.user.email}</email>{/if}
       </author>
       <title>{$action.user_name}'s week {$action.week} bet: {$action.team.abbreviation}</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
           <p>{$action.user_name} bet on the {$action.team.home} {$action.team.team} in week {$action.week}.</p>
         </div>
       </content>
     {elseif $action.action == 'edit'}
     	<author>
	  <name>{$action.admin_name}</name>
	  {if $action.admin.email}<email>{$action.admin.email}</email>{/if}
	</author>
       {if $action.from_team && $action.to_team}
       <title>Edit: {$action.user_name}'s week {$action.week} bet changed from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} changed {$action.user_name}'s week {$action.week} bet from the {$action.from_team.home} {$action.from_team.team} to the {$action.to_team.home} {$action.to_team.team}.</p>
	 </div>
       </content>
       {elseif $action.from_team}
       <title>Edit: {$action.user_name}'s week {$action.week} bet {$action.from_team.abbreviation} removed</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} removed {$action.user_name}'s week {$action.week} bet on the {$action.from_team.home} {$action.from_team.team}.</p>
	 </div>
       </content>
       {elseif $action.to_team}
       <title>Edit: {$action.user_name}'s week {$action.week} bet {$action.to_team.abbreviation} added</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} added the {$action.to_team.home} {$action.to_team.team} as {$action.user_name}'s week {$action.week} bet.</p>
	 </div>
       </content>
       {/if}
     {elseif $action.action == 'addentrant'}
       <title>Edit: {$action.user_name} added to pool</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} added {$action.user_name} to the pool.</p>
	 </div>
       </content>
     {elseif $action.action == 'removeentrant'}
       <title>Edit: {$action.user_name} removed from pool</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} removed {$action.user_name} from the pool.</p>
	 </div>
       </content>
     {/if}
   </entry>
 {/foreach}

{/foreach}

</feed>
