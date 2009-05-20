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
	$('#m-logout').click(function(){
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
//	$('textarea').livequery(function(){
//		nicEditors.allTextAreas({
//			fullPanel: true,
//			iconsPath : 'includes/contrib/nicedit/nicEditorIcons.gif'
//		}); 
//	});
	$('input[name=submit]').live('click',function(){
		nanobyte.submitForm($(this).parents('form'));
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
			},
			add: function(event, ui) {
		        $(this).tabs('select', '#' + ui.panel.id);
				$('a[href=#'+ui.panel.id+']').after('<a style="padding:0" class="tabClose" id="tab_'+ui.index+'">X</a>');
		    }
		});
	});
	$('.tabClose').live('click',function(){
		var panelId = $(this).attr('id').replace(/tab_/,'');
		$(this).parents('.tabs').tabs('remove',panelId).tabs('select',panelId-1);
		$(this).remove();
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
	$('#pager a').livequery(function(){
		$(this).click(function(){
			var me = $(this);
			me.parents('.ui-tabs-panel:first').fadeOut();
			$.ajax({
				url: me.attr('href')+'/ajax',
				dataType: 'json',
				success: function(r){
					console.log(r.title);
					me.parents('.ui-tabs-panel:first').html(r.content).fadeIn();
				}
			})
			return false;
		});
	})

});
