{include file="header.tpl" title="Home"}
{if $file}
    {include file="$file"}
{else}
	{if $posts}
		{foreach from=$posts item=post}
			{include file="post.tpl"}
		{/foreach}
	{elseif $post}
		{include file="post.tpl"}
		{foreach from=$comments item=comment}
			{$comment}
		{/foreach}
	{/if}
{/if}
{include file="footer.tpl"}
