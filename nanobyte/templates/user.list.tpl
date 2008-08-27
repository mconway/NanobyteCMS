<div id="userlist">
	<h2>New users</h2>
	<ul>
		{section name=mysec loop=$users}
		{strip}
		    <li>{$users[mysec].username|capitalize}</li>
		{/strip}
		{/section}
	</ul>
</div>