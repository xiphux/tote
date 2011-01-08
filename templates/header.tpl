<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title>Tote</title>
<link rel="stylesheet" href="css/ext/jquery.qtip.css" type="text/css" />
  <link rel="stylesheet" href="css/tote.css" />
  <link rel="stylesheet" href="css/toteskin.css" />
  {if $source == 'pool'}
  <link rel="stylesheet" href="css/scoreticker.css" />
  {/if}
  {if $csrftoken && $jstoken}
  <script type="text/javascript">
    var TOTE_CSRF_TOKEN="{$csrftoken}"
  </script>
  {/if}
  <script type="text/javascript" src="js/ext/jquery-1.4.2.min.js"></script>
  {if $source == 'pool'}
<script type="text/javascript" src="js/ext/jquery.qtip.min.js"></script>
<script type="text/javascript" src="js/pool.js"></script>
{if !$mobile}
<script type="text/javascript" src="js/poolfull.js"></script>
<script type="text/javascript" src="js/scoreticker.js"></script>
{/if}
{elseif $source == 'bet'}
<script type="text/javascript" src="js/bet.js"></script>
{elseif $source == 'editpool'}
<script type="text/javascript" src="js/ext/jquery.qtip.min.js"></script>
<script type="text/javascript" src="js/editpool.js"></script>
{elseif $source == 'newuser'}
<script type="text/javascript" src="js/newuser.js"></script>
{elseif $source == 'editusers'}
<script type="text/javascript" src="js/editusers.js"></script>
{/if}
 {if $poolinfo}
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (Atom)" href="{$SCRIPT_NAME}?a=atom&p={$pool._id}" type="application/atom+xml" />
 <link rel="alternate" title="{$pool.name} [{$pool.season}-{$pool.season+1}] action log (RSS)" href="{$SCRIPT_NAME}?a=rss&p={$pool._id}" type="application/rss+xml" />
 {/if}
</head>

<body>

<div id="main">
<div id="main2" class="mainShadow{if $small} smallContent{/if}">

{if $header}
<div class="header">
{$header}
</div>
{/if}

<div id="main3">

