/**
 * @author Michael
 */
var i = 0;
$(document).ready(function(){
	nanobyte.initLoader();
	
	$('#loginform').dialog({
		autoOpen : false,
		title: 'Please Log In',
		show: 'puff',
		hide: 'puff',
		height: 200,
		width: 400,
		modal: true,
		closeOnEscape: false,
		buttons: {
			'Log In' : function(){
				if(nanobyte.initValidate()===true){
					$.ajax({
						url : $('#loginform form').attr('action')+'/ajax',
						data: $('#loginform form').serialize(),
						type: 'post',
						dataType: 'json',
						success: function(r){
							if(r.messages){
								nanobyte.showInlineMessage(r.messages);
								if(r.content=='reload'){
									window.document.location.reload();
								}else{
									$('#loginform form')[0].reset();
								}
							}
						}
					})
				}else{
					return false;
				}
			}
		}
	});
	$('#menu-login').click(function(){
		$('#loginform').dialog('open');
		return false;
	});
	$('#menu-logou').click(function(){
		var me = $(this);
		var btns = {
			'Log Out': function(){
				window.location.href = me.attr('href');
				$(this).dialog('close');
			},
			'Stay Put!': function(){
				$(this).dialog('close');
			}
		}
		nanobyte.displayMessage('Are you sure you want to log out?','Proceed to log Out?',btns)
		return false;
	});
	$('#messages').addClass('hidden');
	$('#menu-reg').click(function(){
		$('#loading').dialog('open');
		$.ajax({
			url: $(this).attr('href')+'/ajax',
			type: 'get',
			dataType: 'json',
			success: function(r){
				nanobyte.displayMessage(r.content,'Register Form');
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
	$('input[name=submit]').live('click',function(){
		nanobyte.submitForm($(this));
		return false;
	});
	$('input[name=cancel]').live('click',function(){
		$(this).parents('tr:first').remove();
		if($('input[name=menuname]').length>0){
			$('input[name=menuname]').parent().html($('input[name=menuname]').val());
		}
		return false;
	});
	
 	$("#accordion").accordion({ header: "h3" });
	$('.tabs').livequery(function(){
		$(this).tabs({
			ajaxOptions: { 
				type: 'POST',
				data: 'actions=/ajax'
			} ,
			select : function(event, ui){
				nanobyte.ui = ui;
			},
			fx : {
				opacity : 'toggle'
			}
		});
	});
	$('.toggle').live('click',function(){
		var img = $(this).children('img');
		$(this).parent().next().slideToggle('slow');
		if(img.attr('src')=='templates/NanobyteBlue/images/enable-16.png'){
			img.attr('src','templates/NanobyteBlue/images/disable-16.png');
		}else{
			img.attr('src','templates/NanobyteBlue/images/enable-16.png');
		}
	})
});
