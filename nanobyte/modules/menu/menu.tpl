<div class="block">
	<div class="contentheader">
		<h2 class="title">Menu</h2>
		<span class="toggle"><img src="templates/NanobyteBlue/images/disable-16.png"/></span>
	</div>
	<div class="block-body">
		<div id="menu-accordion">
			{foreach from=$menusblock item=menuArray key=mkey}
		    <h3><a href="#">{$mkey|capitalize} Menu</a></h3>
			<div>
				<ul>
				{foreach from=$menuArray item=mlink}
			   		<li><a href="{$mlink.linkpath}" class="{$mlink.class}" id="{$mlink.styleid}">{$mlink.linktext}</a></li>
				{/foreach}
				</ul>
			</div>
			{/foreach}
		</div>
	</div>
</div>