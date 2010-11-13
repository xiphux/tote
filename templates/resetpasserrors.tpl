{include file='header.tpl'}

{if $errors}
{foreach from=$errors item=error}
{$error}<br />
{/foreach}
{/if}

{include file='footer.tpl'}
