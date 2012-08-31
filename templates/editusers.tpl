{include file='header.tpl' source='editusers' header='Manage Your Users' homelink=true}

<div><a href="{$SCRIPT_NAME}?a=newuser">Add a new user</a></div>

<div>
<table class="displayTable userTable">
<thead>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Admin</th>
<th>Created</th>
<th>Last Login</th>
<th>Last Password Change</th>
<th>Actions</th>
</thead>
<tbody>
{foreach from=$allusers item=eachuser}
<tr class="{cycle values=light,dark}">
 <td>{$eachuser.readable_name}</td>
 <td class="username">{$eachuser.username}</td>
 <td>{$eachuser.email}</td>
 <td>{if $eachuser.admin}Yes{/if}</td>
 <td>{if $eachuser.createdlocal}{$eachuser.createdlocal->format('c')}{else}<span class="nodata">Unknown</span>{/if}</td>
 <td>{if $eachuser.lastloginlocal}{$eachuser.lastloginlocal->format('c')}{else}<span class="nodata">Never</span>{/if}</td>
 <td>{if $eachuser.lastpasswordchangelocal}{$eachuser.lastpasswordchangelocal->format('c')}{else}<span class="nodata">Never</span>{/if}</td>
 <td class="action"><a href="{$SCRIPT_NAME}?a=edituser&u={$eachuser._id}">Edit</a> <a href="{$SCRIPT_NAME}?a=deleteuser&u={$eachuser._id}&csrftoken={$csrftoken}" class="deleteLink">Delete</a></td>
</tr>
{/foreach}
</tbody>
</table>
</div>

{include file='footer.tpl'}
