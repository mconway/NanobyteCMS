<div id="profile-info">
	<div class="fields"><h2>{$name|capitalize}</h2></div>
	<div class="fields"><img id="avatar" src="{$avatar}"/></div>
	<div class="fields">Location:{$location|capitalize}</div>
	<div class="fields">Email: <a href="mailto:{$email}">{$email}</a></div>
	<div class="fields">Last Login: {$lastlogin}</div>
	<div class="fields"><img src="./templates/NanobyteBlue/images/{$online}.png"/> {$online|capitalize}</div>
</div>

<!--<div id="profile-tabs">
	<ul id="uitabs">
		<li><a href="#profile-about"><span>About Me</span></a></li>
		<li><a href="user/profiles/edit/11/ajax"><span>Edit Profile</span></a></li>
		<li><a href="#profile-3"><span>Tab 3</span></a></li>
	</ul>
	<div id="profile-about">
		<div>
			<h2>About Me:</h2>
			{$about}
		</div>
	</div>

</div>-->

<div id="profile-tabs" class="tabs" class="ui-tabs">
	<ul id="uitabs">
		{foreach from=$tabs item=link}
			<li class="tab">{$link}</li>
		{/foreach}
	</ul>

</div>