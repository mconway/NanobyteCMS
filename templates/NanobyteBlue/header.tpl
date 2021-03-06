<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>{$sitename}::{$title | default: 'Untitled'}</title>
		<base href="{php}print SITE_DOMAIN.'/'.PATH{/php}" />
		<link rel="alternate" type="application/rss+xml" title="{$sitename} Feed" href="{$feedurl}" />
		{$includes}
		{*foreach from=$css item=inc}
			<link type="text/css" rel="stylesheet" href="{eval var=$inc}" />
		{/foreach}
		{foreach from=$js item=script}
			<script type="text/javascript" src="{eval var=$script}"></script>
		{/foreach*}
		<script type="text/javascript">{$extraScript}</script>
	</head>
	<body>
	<div id="header">
		<div id="banner">
			{if $logo!=''}<div id="logo"><img src="{$logo}"></div>
			{else}<div id="sitename">{$sitename}</div>
			<div id="siteslogan">{$siteslogan}</div>{/if}
		</div>
		{*<div id="menu">
			{foreach from=$menu item=link}
				{$link}
			{/foreach}
		</div>*}
		{if $noSess}<div id="rbbox">{include file="user.login.tpl"}</div>{/if}
	</div>
	<div id="messages" title="Messages">
		{include file='messages.tpl'}
	</div>
	<div id="main">
		<div id="loading" class="loading">
			<img id="loadingImg" src="templates/NanobyteBlue/images/loading.gif"/>
		</div>
