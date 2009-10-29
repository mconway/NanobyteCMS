<div id="{$page}-form">
	{if $tabbed}
	<div class="tabs">
		<ul>
			{foreach from=$tabbed item=link key=id}
				<li class="tab"><a href="#section{$id}">{$link}</a></li>
			{/foreach}
		</ul>
	
	{/if}
	<form name="{$form->name}" method="{$form->method}" action="{$form->action}" target="frame">
		<div class="formcontainer">
			{foreach from=$form->elements item=group key=id}
				<div class="section" id="section{$id}">
					<div class="formheader">{$group.header}</div>
					{foreach from=$group.elements item=element}
						{if $element.type!=='submit'}
						<div class="elementcontainer {if $element.type=='hidden'}hidden{/if}">
							<div class="label">{if $element.required}
								<span style="color:#FF0000;font-size:16px">*</span>
		   						{/if}
								{$element.label}
							</div>
							{if $element.type == 'textarea'}<br /><br />{/if}
							<div class="element {if $element.required} required{/if}" {if $element.type=='textarea'}style="margin-left:0"{/if} >
								{if $element.type == 'select'}
									{html_options name=$element.name options=$element.list selected=$element.value}
								{elseif $element.type == 'textarea'}
									<textarea name="{$element.name}" 
									{foreach from=$element.options item=option key=name}
										{$name}="{$option}" 
									{/foreach}
									>{$element.value}</textarea>
								{else}
									<input type="{$element.type}" name="{$element.name}" value="{$element.value}" 
									{foreach from=$element.options item=option key=name}
										{$name}="{$option}" 
									{/foreach}
									/>
								{/if}
								{$element.error}
							</div>
							{if $element.error}
							<div class="formerror">{$element.error}</div>
							{/if}
						</div>
						{else}
						</div></div>
						<div class="formbutton"><input type="{$element.type}" name="{$element.name}" value="{$element.value}"
							{foreach from=$element.options item=option key=name}
								{$name}="{$option}" 
							{/foreach}
						/>
						</div>
						{/if}
					{/foreach}
				<!--</div>-->
			{/foreach}
		<!--</div>-->
		<p>{$form->requirednote}</p>
	</form>
</div>
<iframe id="frame" style="display:none"></iframe>
{if $tabbed}
</div>
{/if}
{*debug*}
