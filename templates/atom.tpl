<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="en">
  <title>{$pool.name} [{$pool.season}-{$pool.season+1}]</title>
  <subtitle type="text">{$pool.name} [{$pool.season}-{$pool.season+1}] recent actions</subtitle>
  <link href="{$self}?p={$pool.id}" />
  <link rel="self" href="{$self}?a=atom&amp;p={$pool.id}" />
  <id>{$self}?p={$pool.id}</id>
  {if $updated}
  <updated>{$updated->format('c')}</updated>
  {/if}

{foreach from=$actions item=action}
   <entry>
     <id>tag:{$domain},{$action.time->format('Y-m-d')}:{$pool.id}:{$action.time->format('U')}:{$action.week}:{$action.user_id|escape:'url'}</id>
     <published>{$action.time->format('c')}</published>
     <updated>{$action.time->format('c')}</updated>
     {if $action.action == 4}
       <author>
         <name>{$action.username}</name>
	 {if $action.user_email}<email>{$action.user_email}</email>{/if}
       </author>
       <title>{$action.username}'s week {$action.week} pick: {$action.team_abbr}</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
           <p>{$action.username} picked the {$action.team_name} in week {$action.week}.</p>
         </div>
       </content>
     {elseif $action.action == 5}
     	<author>
	  <name>{$action.admin_username}</name>
	  {if $action.admin_email}<email>{$action.admin_email}</email>{/if}
	</author>
       {if $action.old_team_id && $action.team_id}
       <title>Edit: {$action.username}'s week {$action.week} pick changed from {$action.old_team_abbr} to {$action.team_abbr}</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_username} changed {$action.username}'s week {$action.week} pick from the {$action.old_team_name} to the {$action.team_name}.</p>
	 </div>
       </content>
       {elseif $action.old_team_id}
       <title>Edit: {$action.username}'s week {$action.week} pick {$action.old_team_abbr} removed</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_username} removed {$action.username}'s week {$action.week} pick of the {$action.old_team_name}.</p>
	 </div>
       </content>
       {elseif $action.team_id}
       <title>Edit: {$action.username}'s week {$action.week} pick {$action.team_abbr} added</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_username} added the {$action.team_name} as {$action.username}'s week {$action.week} pick.</p>
	 </div>
       </content>
       {/if}
     {elseif $action.action == 1}
     	<author>
	  <name>{$action.admin_username}</name>
	  {if $action.admin_email}<email>{$action.admin_email}</email>{/if}
	</author>
       <title>Edit: {$action.username} added to pool</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_username} added {$action.username} to the pool.</p>
	 </div>
       </content>
     {elseif $action.action == 2}
     	<author>
	  <name>{$action.admin_username}</name>
	  {if $action.admin_email}<email>{$action.admin_email}</email>{/if}
	</author>
       <title>Edit: {$action.username} removed from pool</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_username} removed {$action.username} from the pool.</p>
	 </div>
       </content>
     {elseif $action.action == 3}
     	<author>
	  <name>{$action.admin_username}</name>
	  {if $action.admin_email}<email>{$action.admin_email}</email>{/if}
	</author>
       {if $action.newpooladmin == 2}
         {if $action.oldpooladmin == 1}
	       <title>Edit: {$action.username} changed from pool administrator to non-voting pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_username} changed {$action.username} from pool administrator to non-voting pool administrator.</p>
		 </div>
	       </content>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.username} set as non-voting pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_username} set {$action.username} as a non-voting pool administrator.</p>
		 </div>
	       </content>
	 {/if}
       {elseif $action.newpooladmin == 1}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.username} changed from non-voting pool administrator to pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_username} changed {$action.username} from non-voting pool administrator to pool administrator.</p>
		 </div>
	       </content>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.username} set as pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_username} set {$action.username} as a pool administrator.</p>
		 </div>
	       </content>
	 {/if}
       {elseif $action.newpooladmin == 0}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.username} removed from non-voting pool administrators</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_username} removed {$action.username} from the non-voting pool administrators.</p>
		 </div>
	       </content>
	 {elseif $action.oldpooladmin == 1}
	       <title>Edit: {$action.username} removed from pool administrators</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_username} removed {$action.username} from the pool administrators.</p>
		 </div>
	       </content>
	 {/if}
       {/if}
     {/if}
   </entry>
{/foreach}

</feed>
