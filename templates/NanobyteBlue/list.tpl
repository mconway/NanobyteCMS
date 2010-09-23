<div id="{$page}list">
	<form action="{$formAction}" method="post">
	<div id="action-links">
	{if $sublinks}
		{foreach from=$sublinks item=link}
			{$link}
		{/foreach}
	{/if}
	</div>
	{if $list_title}<h2>{$list_title}</h2>{/if}
		<table cellspacing="0" {if $tableclass}class='{$tableclass}'{/if}>
			<thead>
				<tr>
					<th {if $cb !== true}style="display:none"{/if}><input type="checkbox" value=""/></th>
					{foreach from=$list.0 key=key item=item}
						{strip}
							<th {if $key=='id' && $showID!=true}style="display:none"{/if}>{$key|capitalize}</th>
						{/strip}
					{/foreach}
				</tr>
			</thead>
			<tbody {if $tableclass}id='{$tableclass}'{/if}>
			{foreach from=$list item=item}
				<tr class="{cycle values="evenrow, oddrow"}" id='{$page}_{$item.id}'>
					<td {if $cb !== true}style="display:none"{/if}><input type="checkbox" name="{$page}[]" value="{$item.id}"/></td>
					{foreach from=$item item=object key=key}
						{strip}
						    <td class="{$key}" {if $key=='id' && $showID!=true}style="display:none"{/if}>{$object}</td>
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
	<div id="pager">
		{$pager}
	</div>
</div>
{if $generated}Page Generated in: {$generated}{/if}
{if $tableclass=='sortable'}{literal}
<script type="text/javascript">
	$('table.sortable tbody').load(function(){
		console.log('load!')
		$(this).sortable();
		$('td, th').each(function(){ $(this).css('width',$(this).width()); });
	});
</script>
{/literal}{/if}
{*debug*}