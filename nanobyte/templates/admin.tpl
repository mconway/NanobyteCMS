{include file="header.tpl" title="Administration"}
	{if $auth == 1}
	<div id="iconmenu">
		{* Loop uls for the menu, then make each with a unique ID *}
		{foreach from=$links key=key item=link}
		<a href="{$link}"><img src="./templates/images/{$key}.png" title="{$key|capitalize}" alt="{$key}" /></a>
		{/foreach}
	</div>
	<div id="content">
		{if $file}
			{include file="$file"}
		{/if}
	</div>
	{/if}
	{if $test}
	{$test}
	{/if}
{include file="footer.tpl"}
