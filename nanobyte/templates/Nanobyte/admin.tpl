{include file="header.tpl" title="Administration"}
<div id="iconmenu">
	{* Loop uls for the menu, then make each with a unique ID *}
	{foreach from=$links key=key item=link}
	<a href="{$link}"><img src="./templates/Nanobyte/images/{$key}.png" title="{$key|capitalize}" alt="{$key}" /></a>
	{/foreach}
</div>
<div id="container">
	<div id="content">
		{$content}
	</div>
</div>
{include file="footer.tpl"}
