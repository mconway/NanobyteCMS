{if $noSess}
<div id="loginform">
	<div id="mcont">
		<div class="messages hidden"></div>
	</div>
	<form name="form" method="POST" action="user/login">
		<span class="required"><label class="label">Username:</label><input type="text" name="user" size="15"/></span><br>
		<span class="required"><label class="label">Password:</label><input class="required" type="password" name="pass" size="15"/></span>
		<!--<input type="submit" name="login" value="Login"/>-->
	</form>
</div>
{/if}
