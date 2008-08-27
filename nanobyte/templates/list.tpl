<h2>{$page|capitalize}</h2>
<div id="{$page}list">
	<form action="{$self}" method="post">
	{if $extra}
		{eval var=$extra}
	{/if}
		<table class="tablesorter">
			<thead>
				<tr>
					<th><input type="checkbox" value=""/></th>
					{foreach from=$list.0 key=key item=item}
						{strip}
							<th>{$key|capitalize}</th>
						{/strip}
					{/foreach}
				</tr>
			</thead>
			<tbody>
			{foreach from=$list item=item}
				<tr>
					<td><input type="checkbox" name="{$page}[]" value="{$item.id}"/></td>
					{foreach from=$item item=object key=key}
					{strip}
					    <td class="{$key}">{$object}</td>
					{/strip}
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</form>
{$pager}
</div>