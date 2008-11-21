<div class="post">
	<div class="contentheader">
		<h2 class="title">
			{if $post.url}<a href="{$post.url}">{$post.title}</a>{else}{$post.title}{/if}
		</h2> 
	</div>
	{if $post.picture}
	{$post.picture}
	{/if}  
	{*$post.terms*}
	<div class="content">
		{$post.body}
	</div>
	<div class="links">
		{if $post.numcomments}<div class="comments"><a href="{$post.url}">{$post.numcomments}</a></div>{/if}
		<div class="submitted">Posted {$post.created} by {$post.author|capitalize}</div> 
		{if $post.modified}
			<div class="modified">Last Modified on {$post.modified}</div>
		{/if}
	</div>
</div>