/**
 * @author Michael
 */
var i = 0;
$(document).ready(function(){
	$('#loginform').css('display','none');
	$('#messages').css('border','none').dialog({
		autoOpen: false,
		buttons: {
			"Ok": function(){
				$('#messages').dialog("close");
			}
		},
		height: 100,
		width: 300
	});
	$('#menu-login').click(function(){
		displayMessage('Please Log In..',$('#loginform').html(),100,300);
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
	$('#menu_login').click(function () {
		$('#menu_login').hide('fast');
		$('#loginform').show('slow');
		return false;
	});
	$('#menu-reg').click(function(){
		$('#loading').dialog('open');
		$.ajax({
			url: $(this).attr('href')+'/ajax',
			type: 'get',
			success: function(html){
				displayMessage('Register Form', html,350,418);
				$('#loading').dialog('close');
			}
		});
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
function displayMessage(t,msg,h,w){
	$('#main').append('<div id="dialog'+i+'" class="dialog">'+msg+'</div>');
	formatMessages();
	$('#dialog'+i).dialog({
		modal: true,
		overlay: {
			opacity: 0.7,
			background: "black"
		},
		resizable: false,
		height: h,
		width: w,
		title: t,
		close: function(){
			$('#dialog'+i).dialog('destroy').remove();
			i--;
		}
	});
	i++;
}
function validate(){
	$('.required').each(function(){
		if ($(this).children('input').val() == ''){
			$(this).children('input').focus();
			var msg = 'You must enter '+$(this).siblings('.label').text()+'!';
			displayMessage('Form Error',msg,100,200);
			return false;
		}
	});
}
function formatMessages(){
	$('.formheader').remove();
	$('.section').css('background-color',$('.ui-dialog').css('background-color'));
}
