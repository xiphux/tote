{include file='header.tpl' source='editusers'}

<div id="main">
<div id="main2">

<div class="header">
Manage Your Users
</div>

<div id="main3">

<div><a href="{$SCRIPT_NAME}?a=newuser">Add a new user</a></div>

<div>
<table class="displayTable userTable">
<thead>
<th>First name</th>
<th>Last name</th>
<th>Username</th>
<th>Email</th>
<th>Admin</th>
<th>Actions</th>
</thead>
<tbody>
{foreach from=$allusers item=eachuser}
<tr class="{cycle values=light,dark}">
 <td>{$eachuser.first_name}</td>
 <td>{$eachuser.last_name}</td>
 <td class="username">{$eachuser.username}</td>
 <td>{$eachuser.email}</td>
 <td>{if $eachuser.admin}Yes{/if}</td>
 <td class="action"><a href="{$SCRIPT_NAME}?a=edituser&u={$eachuser._id}">Edit</a> <a href="{$SCRIPT_NAME}?a=deleteuser&u={$eachuser._id}&csrftoken={$csrftoken}" class="deleteLink">Delete</a></td>
</tr>
{/foreach}
</tbody>
</table>
</div>

</div>
</div>
</div>

{include file='footer.tpl'}
