<div class="block">
	<div class="contentheader">
		<h2 class="title">Users Online</h2>
		<span class="toggle"><img src="templates/NanobyteBlue/images/disable-16.png"/></span>
	</div>
	<div class="block-body">
		<b><i>{$userstr}</i></b>
		<ul id="usersonline">
		{foreach from=$usersonline item=user}
			<li>{$user|capitalize}</li>
		{/foreach}
		</ul>
	</div>
</div>