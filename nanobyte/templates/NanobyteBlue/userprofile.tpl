<div id="profile-info">
	<div class="fields"><h2>{$name|capitalize}</h2></div>
	<div class="fields"><img id="avatar" src="{$avatar}"/></div>
	<div class="fields"><b>Location:</b> {$location|capitalize}</div>
	{*}<div class="fields">Email: <a href="mailto:{$email}">{$email}</a></div>{*}
	<div class="fields"><b>Last Login:</b> <br/>{$lastlogin}</div>
	<div class="fields"><img src="./templates/NanobyteBlue/images/{$online}.png"/> {$online|capitalize}</div>
	<br />
	{if $twitter!='' || $facebook!=''}
	<div class="fields">
		{if $facebook !=''}
		<a href="{$facebook}"><img alt="Facebook" title="Facebook" src="./templates/NanobyteBlue/images/facebook_32.png"/></a>
		{/if}
		{if $twitter!=''}
		<a href="{$twitter}"><img alt="Twitter" title="Twitter" src="./templates/NanobyteBlue/images/twitter_32.png"/></a>
		{/if}
	</div>
	{/if}
</div>

<div id="profile-tabs" class="tabs" class="ui-tabs">
	<ul id="uitabs">
		{foreach from=$tabs item=link}
			<li class="tab">{$link}</li>
		{/foreach}
	</ul>
</div>
{*debug*}