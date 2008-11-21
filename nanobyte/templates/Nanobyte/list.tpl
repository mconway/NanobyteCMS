<h2>{$page|capitalize}</h2>
{if $tabs}
	<ul id="tabs">
		{foreach from=$tabs item=link key=id}
			<li class="tab"><a href="admin/content/{$link}">{$link}</a></li>
		{/foreach}
	</ul>
{/if}
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
						{strip}
							<th>{$key|capitalize}</th>
						{/strip}
					{/foreach}
				</tr>
			</thead>
			<tbody>
			{foreach from=$list item=item}
				<tr>
					{if $cb == true}<td><input type="checkbox" name="{$page}[]" value="{$item.id}"/></td>{/if}
					{foreach from=$item item=object key=key}
					{strip}
					    <td class="{$key}">{$object}</td>
					{/strip}
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
	{if $extra}
		{eval var=$extra}
	{/if}
	</form>
{$pager}
</div>
{*debug*}