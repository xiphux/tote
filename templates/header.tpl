<!DOCTYPE html>
<html lang="en">
<head>

  {if $pool}
  <title>{$pool.name} [{$pool.season}-{$pool.season+1}]</title>
  {else}
  <title>Football Pool</title>
  {/if}

  <link rel="stylesheet" href="css/ext/jquery.qtip.min.css" type="text/css" />

  {if $production}
  <link rel="stylesheet" href="css/tote.min.css" />
  {else}
  <link rel="stylesheet" href="css/tote.css" />
  {/if}

  {if $userstyle}
    {if $production}
    <link rel="stylesheet" href="css/skin/{$userstyle}/toteskin.min.css" />
    {else}
    <link rel="stylesheet" href="css/skin/{$userstyle}/toteskin.css" />
    {/if}
  {elseif $defaultstyle}
    {if $production}
    <link rel="stylesheet" href="css/skin/{$defaultstyle}/toteskin.min.css" />
    {else}
    <link rel="stylesheet" href="css/skin/{$defaultstyle}/toteskin.css" />
    {/if}
  {else}
    {if $production}
    <link rel="stylesheet" href="css/skin/Blue/toteskin.min.css" />
    {else}
    <link rel="stylesheet" href="css/skin/Blue/toteskin.css" />
    {/if}
  {/if}
  
  {if $source == 'pool'}
    {if $production}
    <link rel="stylesheet" href="css/scoreticker.min.css" />
    {else}
    <link rel="stylesheet" href="css/scoreticker.css" />
    {/if}
  {/if}

  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/requirejs@2.3.6/require.min.js"></script>
  <script type="text/javascript">
    {if !$jsmodule}
    {assign var=jsmodule value=common}
    {/if}
    require.config({ldelim}
  	baseUrl: 'js',
    	config: {ldelim}
		{if $csrftoken}
		'editpool': {ldelim}
			csrftoken: '{$csrftoken}'
		{rdelim},
		{/if}
		'common': {ldelim}
			mobile: {if $mobile}true{else}false{/if}
		{rdelim},
		{if $timezoneoffset}
		'modules/scoreticker/localstart': {ldelim}
			timezoneoffset: {$timezoneoffset}
		{rdelim},
		{/if}
		'fullschedule': {ldelim}
			mobile: {if $mobile}true{else}false{/if}
		{rdelim}
	{rdelim},
	paths: {ldelim}
		{if $production}
		{$jsmodule}: '{$jsmodule}.min',
    {else}
		'coffee-script': 'ext/coffee-script',
		cs: 'ext/cs',
		{/if}
		jquery: 'https://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.2/jquery.min',
		qtip: 'ext/jquery.qtip.min',
		cookies: 'ext/jquery.cookies.2.2.0.min',
		d3: 'https://cdn.jsdelivr.net/npm/d3@2.9.6/d3.v2.min',
		modernizr: 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min',
    Vue: 'https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.min',
    axios: 'https://cdn.jsdelivr.net/npm/axios@0.18.0/dist/axios.min'
	{rdelim},
	shim: {ldelim}
		'cookies': {ldelim}
			deps: ['jquery']
		{rdelim}
	{rdelim}
    {rdelim});
    require(['{$jsmodule}']);
  </script>

 {if $poolinfo}
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&amp;p={$pool.id}" type="application/atom+xml" />
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (RSS)" href="{$SCRIPT_NAME}?a=rss&amp;p={$pool.id}" type="application/rss+xml" />
 {/if}

<meta name="viewport" content="initial-scale=1.0" />

</head>

<body>

<div id="main">
<div id="main2" class="mainShadow{if $small} smallContent{/if}">

{if $header}
<div class="header">

<p class="headertext">
{$header}
</p>

{if $homelink || $mainlink}
<p class="links">

{if $mainlink}
{if ($source == 'newuser') || ($source == 'edituser')}
<a href="{$SCRIPT_NAME}?a=editusers">Edit Users</a>
{/if}
{/if}

{if $homelink}
<a href="{$SCRIPT_NAME}{if $pool}?p={$pool.id}{/if}">Home</a>
{/if}

</p>
{/if}

<div class="clear">
</div>

</div>
{/if}

<div id="main3">

