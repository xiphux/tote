{include file='header.tpl' source='editusers' header='Manage Your Users' homelink=true}

{if $user.role == 1}
<div><a href="{$SCRIPT_NAME}?a=newuser">Add a new user</a></div>
{/if}

<div>
<table class="displayTable userTable">
<thead>
<th>
{if $order != "name"}
<a href="{$SCRIPT_NAME}?a=editusers">Name</a>
{else}
Name
{/if}
</th>
<th>
{if $order != "username"}
<a href="{$SCRIPT_NAME}?a=editusers&o=username">Username</a>
{else}
Username
{/if}
</th>
<th>
{if $order != "email"}
<a href="{$SCRIPT_NAME}?a=editusers&o=email">Email</a>
{else}
Email
{/if}
</th>
<th>
{if $order != "role"}
<a href="{$SCRIPT_NAME}?a=editusers&o=role">Role</a>
{else}
Role
{/if}
</th>
<th>
{if $order != "created"}
<a href="{$SCRIPT_NAME}?a=editusers&o=created">Created</a>
{else}
Created
{/if}
</th>
<th>
{if $order != "login"}
<a href="{$SCRIPT_NAME}?a=editusers&o=login">Last Login</a>
{else}
Last Login
{/if}
</th>
<th>
{if $order != "passwordchange"}
<a href="{$SCRIPT_NAME}?a=editusers&o=passwordchange">Last Password Change</a>
{else}
Last Password Change
{/if}
</th>
{if $user.role == 1}
<th>Actions</th>
{/if}
</thead>
<tbody>
{foreach from=$allusers item=eachuser}
<tr class="{cycle values=light,dark}">
 <td>{$eachuser.readable_name}</td>
 <td class="username">{$eachuser.username}</td>
 <td>
 {if $eachuser.email}
 <a href="mailto:{$eachuser.email}">{$eachuser.email}</a>
 {/if}
 </td>
 <td>
 {if $eachuser.role == 1}
 Administrator
 {elseif $eachuser.role == 2}
 Manager
 {else}
 User
 {/if}
 </td>
 <td>{if $eachuser.createdlocal}{$eachuser.createdlocal->format('c')}{else}<span class="nodata">Unknown</span>{/if}</td>
 <td>{if $eachuser.lastloginlocal}{$eachuser.lastloginlocal->format('c')}{else}<span class="nodata">Never</span>{/if}</td>
 <td>{if $eachuser.lastpasswordchangelocal}{$eachuser.lastpasswordchangelocal->format('c')}{else}<span class="nodata">Never</span>{/if}</td>
 {if $user.role == 1}
 <td class="action"><a href="{$SCRIPT_NAME}?a=edituser&u={$eachuser._id}">Edit</a> <a href="{$SCRIPT_NAME}?a=deleteuser&u={$eachuser._id}&csrftoken={$csrftoken}" class="deleteLink">Delete</a></td>
 {/if}
</tr>
{/foreach}
</tbody>
</table>
</div>

{include file='footer.tpl'}
