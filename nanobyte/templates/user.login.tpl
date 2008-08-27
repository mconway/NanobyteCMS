{if $noSess}
<div id="loginform">
	<form name="form" method="POST" action="user/login">
		<input type="text" name="user" size="15"/>
		<input type="password" name="pass" size="15"/>
		<input type="submit" name="login" value="Login"/>
	</form>
	<a href="user/register">Register</a>
</div>
{/if}
