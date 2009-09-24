{include file="header.tpl" title="User"}
{if $file}{include file=$file}
{elseif $content}{$content}
{else}<div id="user-content">{include file="user.login.tpl"}</div>{/if}
{include file="footer.tpl"}
