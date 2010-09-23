<?php

class Mod_RSS{
	public function display(){
		
	}
	
	public function install(){
		
	}
	
	public function uninstall(){
		
	}
}
class RSSController{
	    public static function Display(){
    	$const = get_defined_constants(); // Get all of our application defined constants
		header('Content-type: text/xml'); // Set the Header for XML
		print <<<EOF
		<rss version="2.0">
		<channel>
		<title>{$const['SITE_NAME']}</title>
		<description>{$const['SITE_SLOGAN']}</description>
		<link>{$const['SITE_DOMAIN']}</link>
		<copyright>WiredCMS 2008 Michael Conway - Wiredbyte</copyright>
EOF;
		$content = new Mod_Content();
		$posts = $content->Read($content->getDefaultType(), '1', LIMIT); // Get the Last 10 posts
		foreach ($content->items['content'] as $post){ // Set the pubdate and create an <item></item> for each post
			$pubDate = strftime( "%a, %d %b %Y %T %Z" , $post['created']);
			print <<<EOF
			<item>
		        <title>{$post['title']}</title>
		        <description><![CDATA[{$post['body']}]]></description>
		        <link>{$const['SITE_DOMAIN']}/{$const['PATH']}/posts/{$post['pid']}</link>
		        <pubDate>{$pubDate}</pubDate>
		     </item>  
			 
EOF;
		}
		print '</channel>
		</rss>'; // Close tags and end feed.
    }
}
?>