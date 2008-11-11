/**
 * @author Michael
 */
tinyMCE.init({
	// General options
	mode : "textareas",
	theme : "advanced",
	plugins : "safari,pagebreak,table,advlink,emotion,media,searchreplace,paste,fullscreen,visualchars,imagemanager,filemanager",
	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,|,fullscreen,insertfile,|,insertimage,cleanup,help,code",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image",
	theme_advanced_buttons3 : "styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : false,
	// Drop lists for link/image/media/template dialogs
	template_external_list_url : "js/template_list.js",
	external_link_list_url : "js/link_list.js",
	external_image_list_url : "js/image_list.js",
	media_external_list_url : "js/media_list.js",
	// Replace values for the template plugin
});