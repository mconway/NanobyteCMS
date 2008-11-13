<div id="userlist">
	<ul>
		{section name=mysec loop=$users}
		{strip}
		    <li>{$users[mysec].username|capitalize}</li>
		{/strip}
		{/section}
	</ul>
</div>