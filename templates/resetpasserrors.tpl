{include file='header.tpl'}

<div id="main">
<div id="main2" class="smallContent">

<div id="main3">

{if $errors}
{foreach from=$errors item=error}
{$error}<br />
{/foreach}
{/if}

</div>
</div>
</div>

{include file='footer.tpl'}
