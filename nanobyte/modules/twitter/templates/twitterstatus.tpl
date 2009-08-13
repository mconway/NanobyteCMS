<div class="block">
	<div class="contentheader">
		<h2 class="title">{$twitter->screen_name}'s Twitter</h2>
		<span class="toggle"><img src="templates/NanobyteBlue/images/disable-16.png"/></span>
	</div>
	<div class="block-body">
		{$twitter->status->text}<br />
		{$twit_limit->remaining_hits}/{$twit_limit->hourly_limit} Resets: {$twit_limit->reset_time}
	</div>
</div>
