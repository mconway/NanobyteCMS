<div class="gallery container">
	<div class="left">
		<ul class="items">
		{foreach from=$albums item=album}
			<li>
				<a href="javascript:showContainer('#container-{$album.id}', '{$album.name}')" style="display:block;">
					<span class="thumb" id="id{$album.id}{$album.length}" style="background-image: url('modules/gallery/gallery.inc.php?path={$album.path}&amp;width=80&amp;height=60');"></span>
					<br /><span>{$album.name}</span>
				</a>
			</li>
		{/foreach}
		</ul>
	</div>
	<div class="overlay"></div>
	{foreach from=$albums item=album}
		<div class="right" style="display: none;" id="container-{$album.id}">
		{foreach from=$album.images item=image}
			<img class="thumb" src="modules/gallery/gallery.inc.php?i={$album.path}/{$image}&amp;size=270" id="{$album.path|replace:'/':'_'}_{$image}" alt="{$image}"/>
		{/foreach}
		</div>
	{/foreach}
</div>