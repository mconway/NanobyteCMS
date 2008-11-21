<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>{$sitename}::{$title | default: 'Untitled'}</title>
		<base href="{php}print SITE_DOMAIN.'/'.PATH{/php}" />
		<link rel="alternate" type="application/rss+xml" title="{$sitename} Feed" href="{$feedurl}" />
		{foreach from=$css item=inc}
			<link type="text/css" rel="stylesheet" media="{$inc.media}" href="{$inc.file}" />
		{/foreach}
		{foreach from=$js item=script}
			<script type="text/javascript" src="{$script}"></script>
		{/foreach}
	</head>
	<body>
	<div id="header">
		<div id="banner">
			<div id="logo"></div>
			<div id="sitename">{$sitename}</div>
			<div id="siteslogan">{$siteslogan}</div>
		</div>
		<div id="menu">
			{foreach from=$menu item=link}
				{$link}
			{/foreach}
		</div>
		{if $noSess}<div id="rbbox">{include file="user.login.tpl"}</div>{/if}
	</div>
	
	{if $messages}
	 	<div id="messages" title="Messages" class="flora">
			{include file='messages.tpl'}
		</div>
	{/if}
	<div id="main">
