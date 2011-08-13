<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
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

  {if $csrftoken && $jstoken}
  <script type="text/javascript">
    var TOTE_CSRF_TOKEN="{$csrftoken}"
  </script>
  {/if}

  <script type="text/javascript" src="js/ext/jquery-1.4.2.min.js"></script>
  <script type="text/javascript" src="js/ext/jquery.cookies.2.2.0.min.js"></script>

  {if $source == 'pool'}
    <script type="text/javascript" src="js/ext/jquery.qtip.min.js"></script>
    {if file_exists('js/pool.min.js')}
    <script type="text/javascript" src="js/pool.min.js"></script>
    {else}
    <script type="text/javascript" src="js/pool.js"></script>
    {/if}
    {if !$mobile || $forcefull}
      {if file_exists('js/poolfull.min.js')}
      <script type="text/javascript" src="js/poolfull.min.js"></script>
      {else}
      <script type="text/javascript" src="js/poolfull.js"></script>
      {/if}
      {if file_exists('js/scoreticker.min.js')}
      <script type="text/javascript" src="js/scoreticker.min.js"></script>
      {else}
      <script type="text/javascript" src="js/scoreticker.js"></script>
      {/if}
    {/if}
  {elseif $source == 'bet'}
    {if file_exists('js/bet.min.js')}
    <script type="text/javascript" src="js/bet.min.js"></script>
    {else}
    <script type="text/javascript" src="js/bet.js"></script>
    {/if}
  {elseif $source == 'editpool'}
    <script type="text/javascript" src="js/ext/jquery.qtip.min.js"></script>
    {if file_exists('js/editpool.min.js')}
    <script type="text/javascript" src="js/editpool.min.js"></script>
    {else}
    <script type="text/javascript" src="js/editpool.js"></script>
    {/if}
  {elseif $source == 'newuser'}
    {if file_exists('js/newuser.min.js')}
    <script type="text/javascript" src="js/newuser.min.js"></script>
    {else}
    <script type="text/javascript" src="js/newuser.js"></script>
    {/if}
  {elseif $source == 'editusers'}
    {if file_exists('js/editusers.min.js')}
    <script type="text/javascript" src="js/editusers.min.js"></script>
    {else}
    <script type="text/javascript" src="js/editusers.js"></script>
    {/if}
  {/if}
 {if $poolinfo}
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&p={$pool._id}" type="application/atom+xml" />
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (RSS)" href="{$SCRIPT_NAME}?a=rss&p={$pool._id}" type="application/rss+xml" />
 {/if}

<meta name="viewport" content="initial-scale=1.0">

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

