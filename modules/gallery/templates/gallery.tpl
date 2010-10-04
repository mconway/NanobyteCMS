<div id="gallery-container">
	{if $image_list}<span id="galleryback"></span>{/if}
	<h1><a href="gallery"><img src="{$theme_path}/images/back-24.png" title="Back to Gallery"/></a> {$album_title|default:'Albums'}</h1>
	{if $image_list}
		{foreach from=$image_list item='image' key='key'}
			<span id="a_{$key}" class="gallery_image" style="display: inline-block;">
				<a class="lightbox" href="{$image.orig}"><img src="{$image.thumb}" alt="image"></a>
			</span>
		{/foreach}
	{else}
		{foreach from=$album_list item='album' key='key'}
			<span id="a_{$key}" class="gallery_album" style="display: inline-block;">
				<a href="gallery/albums/{$key}"><img src="modules/gallery/images/folder.png" alt="album"><br />{$album}</a>
			</span>
		{/foreach}
	{/if}
</div>
