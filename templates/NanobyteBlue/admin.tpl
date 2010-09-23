{include file="header.tpl" title="Administration"}
<div id="iconmenu">
	<ul>
	{* Loop uls for the menu, then make each with a unique ID *}
		{foreach from=$links key=key item=link}
		<li>{$link}</li>
		{/foreach}
	</ul>
</div>
<div id="container">
	<div id="content">
	{if $tabs}
		{include file="tabs.tpl"}
	{/if}
		{$content}
	</div>
</div>
{include file="footer.tpl"}
