<div class="post">
	<h2 class="title">
		<a href="{$url}">{$post.title}</a>
	</h2> 
	{if $post.picture}
	{$post.picture}
	{/if}  
	{*$post.terms*}
	<div class="content">
		{$post.body}
	</div>
	<div class="links">
		<div class="submitted">Posted {$post.created} by {$post.author|capitalize}</div> 
		{if $post.modified}
			<div class="modified">Last Modified on {$post.modified}</div>
		{/if}
	</div>
</div>