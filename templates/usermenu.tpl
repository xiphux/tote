{if $user}
  <tr><th>Welcome, {if $user.first_name}{$user.first_name} {$user.last_name}{else}{$user.username}{/if}</th></tr>
<tr><td><a href="index.php?a=schedule">Schedule</a></td></tr>
{if $user.role == 1}
<tr><td><a href="index.php?a=update">Update scores</a></td></tr>
<tr><td><a href="index.php?a=editpool&p={$pool._id}">Manage pool</a></td></tr>
<tr><td><a href="index.php?a=editusers">Manage users</a></td></tr>
<tr><td><a href="index.php?a=newpool">New pool</a></td></tr>
{/if}
<tr><td><a href="index.php?a=editprefs">Edit preferences</a></td></tr>
<tr><td><a href="index.php?a=changepass">Change password</a></td></tr>
<tr><td><a href="index.php?a=logout">Logout</a></td></tr>
{else}
<tr><td><a href="index.php?a=login">Login</a></td></tr>
<tr><td><a href="index.php?a=schedule">Schedule</a></td></tr>
{/if}
