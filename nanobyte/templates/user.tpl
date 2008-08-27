{include file="header.tpl" title="User"}
{if $file}{include file=$file}
{else}{include file="user.login.tpl"}{/if}
{include file="footer.tpl"}
