/**
 * @author Michael
 */

 bkLib.onDomLoaded(function() { 
 	nicEditors.allTextAreas({
		fullPanel: true,
		iconsPath : 'includes/contrib/nicedit/nicEditorIcons.gif'
	}); 
 });

$(document).ready(function(){
	$('#tabs').tabs();
	$('#loginform').hide();
/*
	$('#messages').dialog({
		draggable: true
	});
*/
	$('#rbbox').append('<input type="button" value="Log in" id="menu_login"/>');
	$('#menu_login').click(function () {
		$('#menu_login').hide('fast');
		$('#loginform').show('slow');
		return false;
	}
	);
});
