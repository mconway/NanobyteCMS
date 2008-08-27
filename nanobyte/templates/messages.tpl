<ul class="messages">
		{foreach from=$messages item=type key=key}
			{foreach from=$type item=message}
				{strip}
				    <li class="{$key}">{$message}</td>
				{/strip}
			{/foreach}
		{/foreach}
</ul>