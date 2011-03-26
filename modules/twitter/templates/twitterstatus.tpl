{foreach from=$twitter->status item=status}
<img src="{$status->user->profile_image_url}"/>
<div>{$status->text}</div>
<hr/>
{*}{$twit_limit->remaining_hits}/{$twit_limit->hourly_limit} Resets: {$twit_limit->reset_time}{*}
{/foreach}