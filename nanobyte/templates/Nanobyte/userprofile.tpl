<div style="width:35%; height: 100%; background-color: black; float: left;">
	<div class="fields"><h2>{$name|capitalize}</h2></div>
	<div class="fields">{$avatar}</div>
	<div class="fields">Location:{$location|capitalize}</div>
	<div class="fields">Email: <a href="mailto:{$email}">{$email}</a></div>
	<div class="fields">Last Login: {$lastlogin}</div>
	<div class="fields"><img src="./templates/images/{$online}.png"/> {$online|capitalize}</div>
</div>

<div id="container-1">
	<!--<ul id="uitabs">
		<li><a href="#fragment-1"><span></span></a></li>
		<li><a href="#fragment-2"><span></span></a></li>
		<li><a href="#fragment-3"><span></span></a></li>
	</ul>-->
	<div id="fragment-1">
		<div>
			<h2>About Me:</h2>
			{$about}
		</div>
	</div>
	<!--<div id="fragment-2">
		<div></div>
	</div>
	<div id="fragment-3">
		
	</div>-->
</div>