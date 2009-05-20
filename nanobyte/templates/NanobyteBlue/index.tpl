{include file="header.tpl" title="Home"}
{if $file}
    {include file="$file"}
{elseif $posts}
	<div class="content-block" id="news">
		<div class="contentheader">
			<h2 class="title">Latest News</h2>
			<span class="toggle"><img src="templates/NanobyteBlue/images/disable-16.png"/></span>
		</div>
		<div class="tabs">
			<ul>
				{foreach from=$posts item=post}
					<li><a href={$post.url}>{$post.title}</a></li>
				{/foreach}
				<li><a href="#more">More...</a></li>
			</ul>
			<div id="more">More Links Here...</div>
		</div>
	</div>
	<div class="content-block" id="projects">
		<div class="contentheader">
			<h2 class="title">Current Projects</h2>
			<span class="toggle"><img src="templates/NanobyteBlue/images/disable-16.png"/></span>
		</div>
		<div class="tabs">
			<ul>
				<li><a href="{$post.url}">Nanobyte</a></li>
				<li><a href="{$post.url}">jQuery Form Validate</a></li>
				<li><a href="{$post.url}">Wiredbyte</a></li>
			</ul>
		</div>
	</div>
	<div class="content-block" id="other">
		<div class="contentheader">
			<h2 class="title">Other Stuff</h2>
			<span class="toggle"><img src="templates/NanobyteBlue/images/disable-16.png"/></span>
		</div>
		<div class="tabs">
			<ul>
				<li><a href="{$posts.1.url}">Other 1</a></li>
				<li><a href="{$posts.2.url}">Other 2</a></li>
				<li><a href="{$posts.3.url}">Other 3</a></li>
			</ul>
		</div>
	</div>
{elseif $post}
	{include file="post.tpl"}
	{foreach from=$comments item=comment}
		{$comment}
	{/foreach}
{else}
	<div id="content">{$content}</div>
{/if}
{include file="footer.tpl"}
{*debug*}