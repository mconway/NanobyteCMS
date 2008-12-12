/**
 * @author Michael
 */

$(document).ready(function(){
	$('#loginform').addClass('dialog').dialog({
		autoOpen: false,
		modal: true,
		title: 'Please Log In',
		overlay: {
			opacity: 0.7,
			background: "black"
		}
	});
	$('#messages').css('border','none').dialog({
		buttons: {
			"Ok": function(){
				$('#messages').dialog("close");
			}
		},
		height: 100,
		width: 300
	});
	$('#menu-login').click(function(){
		$('#loginform').dialog("open");
		return false;
	});
	$('#loading').dialog({
		autoOpen: false,
		modal: true,
		title: 'Loading, Please wait..',
		draggable: false,
		resizable: false,
		height: 50,
		overlay: {
			opacity: 0.7,
			background: "black"
		}
	});
	//$('#rbbox').append('<input type="button" value="Log in" id="menu_login"/>');
	$('#menu_login').click(function () {
		$('#menu_login').hide('fast');
		$('#loginform').show('slow');
		return false;
	});
	$('textarea').livequery(function(){
		nicEditors.allTextAreas({
			fullPanel: true,
			iconsPath : 'includes/contrib/nicedit/nicEditorIcons.gif'
		}); 
	});
	$('form').livequery(function(){
		$(this).submit(function() {
			var inputs = array();
			$(':input', this).each(function() {
				inputs.push($(this).attr('name') + '=' + $(this).attr('value'));
				console.log($(this).attr('name')+'  '+$(this).val());
				console.log(inputs);
			});
			$.ajax({
				data: inputs,
				url: this.action+'/ajax',
				type: "post",
				timeout: 2000,
				error: function() {
				  console.log("Failed to submit");
				},
				success: function(r) { 
				  $('#main').html(r);
				}
			});
			return false;
		});
	});
});
