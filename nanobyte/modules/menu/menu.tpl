<div id="menu-accordion">
	{foreach from=$menusblock item=menuArray key=mkey}
    <h3><a href="admin">{$mkey|capitalize} Menu</a></h3>
	<div>
		<ul>
		{foreach from=$menuArray item=mlink}
	   		<li><a href="{$mlink.linkpath}" class="{$mlink.class}" id="{$mlink.styleid}">{$mlink.linktext}</a></li>
		{/foreach}
		</ul>
	</div>
	{/foreach}
</div>