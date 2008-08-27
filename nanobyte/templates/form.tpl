<div id="{$page}">
	<h2>{$page|capitalize}</h2>
	<form {$form.attributes}>
		<div class="formcontainer">
			<div>
			{foreach from=$form.sections item=section}
			<div class="formheader">{$section.header}</div>
				{foreach from=$section.elements item=element}
					<div class="elementcontainer">
						{if $element.type=='submit'}
							<div class="formbutton">{$element.html}</div>
						{else}
							<div class="label">{if $element.required}
								<span style="color:#FF0000;font-size:16px">*</span>
	       						{/if}
								{$element.label}
							</div>
							<div class="element">{$element.html}</div>
						{/if}
						{if $element.error}
							<div class="formerror">{$element.error}</div>
						{/if}
					</div>
				{/foreach}
			{/foreach}
			</div>
		</div>
		<p>{$form.requirednote}</p>
	</form>
</div>
{*debug*}
