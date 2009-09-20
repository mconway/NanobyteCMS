<div id=""gallery-container">
	{foreach from=$thumbs_list item='thumb' key='key'}
		<span id="a_{$key}">
			<a><img src="{$thumb}" alt="image"></a>
		</span>
	{/foreach}
</div>
{debug}