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
     <id>tag:{$domain},{$action.time->format('Y-m-d')}:{$pool._id}:{$time}:{$action.week}:{$action.user_name|escape:'url'}</id>
     <published>{$action.time->format('c')}</published>
     <updated>{$action.time->format('c')}</updated>
     {if $action.action == 'bet'}
       <author>
         <name>{$action.user_name}</name>
	 {if $action.user.email}<email>{$action.user.email}</email>{/if}
       </author>
       <title>{$action.user_name}'s week {$action.week} pick: {$action.team.abbreviation}</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
           <p>{$action.user_name} picked the {$action.team.home} {$action.team.team} in week {$action.week}.</p>
         </div>
       </content>
     {elseif $action.action == 'edit'}
     	<author>
	  <name>{$action.admin_name}</name>
	  {if $action.admin.email}<email>{$action.admin.email}</email>{/if}
	</author>
       {if $action.from_team && $action.to_team}
       <title>Edit: {$action.user_name}'s week {$action.week} pick changed from {$action.from_team.abbreviation} to {$action.to_team.abbreviation}</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} changed {$action.user_name}'s week {$action.week} pick from the {$action.from_team.home} {$action.from_team.team} to the {$action.to_team.home} {$action.to_team.team}.</p>
	 </div>
       </content>
       {elseif $action.from_team}
       <title>Edit: {$action.user_name}'s week {$action.week} pick {$action.from_team.abbreviation} removed</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} removed {$action.user_name}'s week {$action.week} pick of the {$action.from_team.home} {$action.from_team.team}.</p>
	 </div>
       </content>
       {elseif $action.to_team}
       <title>Edit: {$action.user_name}'s week {$action.week} pick {$action.to_team.abbreviation} added</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} added the {$action.to_team.home} {$action.to_team.team} as {$action.user_name}'s week {$action.week} pick.</p>
	 </div>
       </content>
       {/if}
     {elseif $action.action == 'addentrant'}
     	<author>
	  <name>{$action.admin_name}</name>
	  {if $action.admin.email}<email>{$action.admin.email}</email>{/if}
	</author>
       <title>Edit: {$action.user_name} added to pool</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} added {$action.user_name} to the pool.</p>
	 </div>
       </content>
     {elseif $action.action == 'removeentrant'}
     	<author>
	  <name>{$action.admin_name}</name>
	  {if $action.admin.email}<email>{$action.admin.email}</email>{/if}
	</author>
       <title>Edit: {$action.user_name} removed from pool</title>
       <content type="xhtml">
         <div xmlns="http://www.w3.org/1999/xhtml">
	   <p>Admin {$action.admin_name} removed {$action.user_name} from the pool.</p>
	 </div>
       </content>
     {elseif $action.action == 'pooladminchange'}
     	<author>
	  <name>{$action.admin_name}</name>
	  {if $action.admin.email}<email>{$action.admin.email}</email>{/if}
	</author>
       {if $action.newpooladmin == 2}
         {if $action.oldpooladmin == 1}
	       <title>Edit: {$action.user_name} changed from pool administrator to non-voting pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_name} changed {$action.user_name} from pool administrator to non-voting pool administrator.</p>
		 </div>
	       </content>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.user_name} set as non-voting pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_name} set {$action.user_name} as a non-voting pool administrator.</p>
		 </div>
	       </content>
	 {/if}
       {elseif $action.newpooladmin == 1}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.user_name} changed from non-voting pool administrator to pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_name} changed {$action.user_name} from non-voting pool administrator to pool administrator.</p>
		 </div>
	       </content>
	 {elseif $action.oldpooladmin == 0}
	       <title>Edit: {$action.user_name} set as pool administrator</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_name} set {$action.user_name} as a pool administrator.</p>
		 </div>
	       </content>
	 {/if}
       {elseif $action.newpooladmin == 0}
         {if $action.oldpooladmin == 2}
	       <title>Edit: {$action.user_name} removed from non-voting pool administrators</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_name} removed {$action.user_name} from the non-voting pool administrators.</p>
		 </div>
	       </content>
	 {elseif $action.oldpooladmin == 1}
	       <title>Edit: {$action.user_name} removed from pool administrators</title>
	       <content type="xhtml">
		 <div xmlns="http://www.w3.org/1999/xhtml">
		   <p>Admin {$action.admin_name} removed {$action.user_name} from the pool administrators.</p>
		 </div>
	       </content>
	 {/if}
       {/if}
     {/if}
   </entry>
 {/foreach}

{/foreach}

</feed>
