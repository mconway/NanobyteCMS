<div id="{$page}-form">
	{if $tabbed}
	<div class="tabs">
		<ul>
			{foreach from=$tabbed item=link key=id}
				<li class="tab"><a href="#section{$id}">{$link}</a></li>
			{/foreach}
		</ul>
	
	{/if}
	<form {$form.attributes} target="frame">
		<div class="formcontainer">
			{foreach from=$form.sections item=section key=id}
			<div class="section" id="section{$id}">
				<div class="formheader">{$section.header}</div>
				{foreach from=$section.elements item=element}
				{if $element.type!=='submit'}
				<div class="elementcontainer">
					<div class="label">{if $element.required}
						<span style="color:#FF0000;font-size:16px">*</span>
   						{/if}
						{$element.label}
					</div>
					<div class="element {if $element.required} required {/if}">{$element.html}</div>
					{if $element.error}
					<div class="formerror">{$element.error}</div>
					{/if}
				</div>
				{else}
				</div><div class="formbutton">{$element.html}
				{/if}
				{/foreach}
			</div>
			{/foreach}
		</div>
		<p>{$form.requirednote}</p>
	</form>
</div>
<iframe id="frame" style="display:none"></iframe>
{if $tabbed}
</div>
{/if}
{*debug*}
