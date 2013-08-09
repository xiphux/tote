<!DOCTYPE html>
<html lang="en">
<head>

  {if $pool}
  <title>{$pool.name} [{$pool.season}-{$pool.season+1}]</title>
  {else}
  <title>Football Pool</title>
  {/if}

  <link rel="stylesheet" href="css/ext/jquery.qtip.min.css" type="text/css" />

  {if file_exists('css/tote.min.css')}
  <link rel="stylesheet" href="css/tote.min.css" />
  {else}
  <link rel="stylesheet" href="css/tote.css" />
  {/if}

  {if $userstyle}
    {if file_exists("css/skin/$userstyle/toteskin.min.css")}
    <link rel="stylesheet" href="css/skin/{$userstyle}/toteskin.min.css" />
    {else}
    <link rel="stylesheet" href="css/skin/{$userstyle}/toteskin.css" />
    {/if}
  {elseif $defaultstyle}
    {if file_exists("css/skin/$defaultstyle/toteskin.min.css")}
    <link rel="stylesheet" href="css/skin/{$defaultstyle}/toteskin.min.css" />
    {else}
    <link rel="stylesheet" href="css/skin/{$defaultstyle}/toteskin.css" />
    {/if}
  {else}
    {if file_exists('css/skin/Blue/toteskin.min.css')}
    <link rel="stylesheet" href="css/skin/Blue/toteskin.min.css" />
    {else}
    <link rel="stylesheet" href="css/skin/Blue/toteskin.css" />
    {/if}
  {/if}
  
  {if $source == 'pool'}
    {if file_exists('css/scoreticker.min.css')}
    <link rel="stylesheet" href="css/scoreticker.min.css" />
    {else}
    <link rel="stylesheet" href="css/scoreticker.css" />
    {/if}
  {/if}

  <script type="text/javascript" src="js/ext/require.js"></script>
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
		{if "js/$jsmodule.min.js"|file_exists}
		{$jsmodule}: '{$jsmodule}.min',
		{/if}
		jquery: [
			{if $googlejs}
			'//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min',
			{/if}
			'ext/jquery-1.8.2.min'
		],
		qtip: 'ext/jquery.qtip.min',
		cookies: 'ext/jquery.cookies.2.2.0.min',
		d3: 'ext/d3.v2.min',
		modernizr: 'ext/modernizr.custom',
		'coffee-script': 'ext/coffee-script',
		cs: 'ext/cs'
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
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&amp;p={$pool._id}" type="application/atom+xml" />
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (RSS)" href="{$SCRIPT_NAME}?a=rss&amp;p={$pool._id}" type="application/rss+xml" />
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
<a href="{$SCRIPT_NAME}{if $pool}?p={$pool._id}{/if}">Home</a>
{/if}

</p>
{/if}

<div class="clear">
</div>

</div>
{/if}

<div id="main3">

