{include file='header.tpl' jsmodule='editusers' header='Manage Your Users' homelink=true}

{if $user.role == 1}
<div><a href="{$SCRIPT_NAME}?a=newuser">Add a new user</a></div>
{/if}

<div>
<table class="displayTable userTable">
<thead>
<tr>
<th>
{if $order != "name"}
<a href="{$SCRIPT_NAME}?a=editusers">Name</a>
{else}
Name
{/if}
</th>
<th>
{if $order != "username"}
<a href="{$SCRIPT_NAME}?a=editusers&amp;o=username">Username</a>
{else}
Username
{/if}
</th>
<th>
{if $order != "email"}
<a href="{$SCRIPT_NAME}?a=editusers&amp;o=email">Email</a>
{else}
Email
{/if}
</th>
<th>
{if $order != "role"}
<a href="{$SCRIPT_NAME}?a=editusers&amp;o=role">Role</a>
{else}
Role
{/if}
</th>
<th>
{if $order != "created"}
<a href="{$SCRIPT_NAME}?a=editusers&amp;o=created">Created</a>
{else}
Created
{/if}
</th>
<th>
{if $order != "login"}
<a href="{$SCRIPT_NAME}?a=editusers&amp;o=login">Last Login</a>
{else}
Last Login
{/if}
</th>
<th>
{if $order != "passwordchange"}
<a href="{$SCRIPT_NAME}?a=editusers&amp;o=passwordchange">Last Password Change</a>
{else}
Last Password Change
{/if}
</th>
{if $user.role == 1}
<th>Actions</th>
{/if}
</tr>
</thead>
<tbody>
{foreach from=$allusers item=eachuser}
<tr class="{cycle values=light,dark}">
 <td>{$eachuser.display_name}</td>
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
 <td>{if $eachuser.created_local}<time datetime="{$eachuser.created_local->format('Y-m-d\TH:i:sO')}">{$eachuser.created_local->format('c')}</time>{else}<span class="nodata">Unknown</span>{/if}</td>
 <td>{if $eachuser.last_login_local}<time datetime="{$eachuser.last_login_local->format('Y-m-d\TH:i:sO')}">{$eachuser.last_login_local->format('c')}</time>{else}<span class="nodata">Never</span>{/if}</td>
 <td>{if $eachuser.last_password_change_local}<time datetime="{$eachuser.last_password_change_local->format('Y-m-d\TH:i:sO')}">{$eachuser.last_password_change_local->format('c')}</time>{else}<span class="nodata">Never</span>{/if}</td>
 {if $user.role == 1}
 <td class="action"><a href="{$SCRIPT_NAME}?a=edituser&amp;u={$eachuser.id}">Edit</a> <a href="{$SCRIPT_NAME}?a=deleteuser&amp;u={$eachuser.id}&amp;csrftoken={$csrftoken}" class="deleteLink">Delete</a></td>
 {/if}
</tr>
{/foreach}
</tbody>
</table>
</div>

<div>
{if $allusers}
<a href="mailto:{foreach from=$allusers item=eachuser name=users}{if $eachuser.email}{if !$smarty.foreach.users.first},{/if}{$eachuser.email}{/if}{/foreach}">Email all</a>
{/if}
</div>

{include file='footer.tpl'}
