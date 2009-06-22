<div id="{$page}list">
	<form action="{$formAction}" method="post">
	<div id="action-links">
	{if $sublinks}
		{foreach from=$sublinks item=link}
			{$link}
		{/foreach}
	{/if}
	</div>
		<table cellspacing="0" {if $tableclass}class='{$tableclass}'{/if}>
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
			<tbody {if $tableclass}id='{$tableclass}'{/if}>
			{foreach from=$list item=item}
				<tr class="{cycle values="evenrow, oddrow"}" id='{$page}_{$item.id}'>
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