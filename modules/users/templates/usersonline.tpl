<b><i>{$userstr}</i></b>
<ul id="usersonline">
{foreach from=$usersonline item=user}
	<li>{$user|capitalize}</li>
{/foreach}
</ul>
