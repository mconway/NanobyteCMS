<div id="{$page}list">
	<form action="{$self}" method="post">
	{if $sublinks}
		{foreach from=$sublinks item=link}
			{$link}
		{/foreach}
	{/if}
		<table class="tablesorter">
			<thead>
				<tr>
					{if $cb == true}<th><input type="checkbox" value=""/></th>{/if}
					{foreach from=$list.0 key=key item=item}
						{if $key!='id' || $showID==true}
							{strip}
								<th>{$key|capitalize}</th>
							{/strip}
						{/if}
					{/foreach}
				</tr>
			</thead>
			<tbody>
			{foreach from=$list item=item}
				<tr>
					{if $cb == true}<td><input type="checkbox" name="{$page}[]" value="{$item.id}"/></td>{/if}
					{foreach from=$item item=object key=key}
					{if $key!='id' || $showID==true}
						{strip}
						    <td class="{$key}">{$object}</td>
						{/strip}
					{/if}
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
	{if $extra}
		{eval var=$extra}
	{/if}
	</form>
	<div id="pager">
		{$pager}
	</div>
</div>
{*debug*}