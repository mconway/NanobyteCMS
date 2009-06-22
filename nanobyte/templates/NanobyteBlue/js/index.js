/**
 * @author Michael
 */
var i = 0;

$(document).ready(function(){
	nanobyte.initLoader();
	
	$.preloadImages('templates/NanobyteBlue/images/list-info.png','templates/NanobyteBlue/images/list-error.png');
	
	$('#loginform').dialog({
		autoOpen : false,
		title: 'Please Log In',
//		show: 'puff',
//		hide: 'puff',
		height: 220,
		width: 400,
		modal: true,
		closeOnEscape: false,
		buttons: {
			'Log In' : function(){
				if(nanobyte.initValidate($('#loginform form'))===true){
					$.ajax({
						url : $('#loginform form').attr('action')+'/ajax',
						data: $('#loginform form').serialize(),
						type: 'post',
						dataType: 'json',
						success: function(r){
							if(r.messages){
								nanobyte.showInlineMessage(r.messages);
								if(r.content=='reload'){
									setTimeout('window.document.location.reload()',2000);
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
	$('#forgot_pw a').click(nanobyte.ajaxcall)
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
				nanobyte.lastUI = nanobyte.ui;
				nanobyte.ui = ui;
			},
			fx : {
				opacity : 'toggle'
			},
			add: function(event, ui) {
		        $(this).tabs('select', '#' + ui.panel.id);
				$('a[href=#'+ui.panel.id+']').after('<a class="tabClose" id="tab_'+ui.index+'"></a>');
		    }
		});
	});
	$('.tabClose').live('click',function(){
		$(this).parents('.tabs').tabs('remove',$(this).attr('id').replace(/tab_/,'')).tabs('select',nanobyte.lastUI.index);
	});
	$('.toggle').live('click',function(){
		var img = $(this).children('img');
		$(this).parent().next().slideToggle('slow');
		var path = 'templates/NanobyteBlue/images/';
		if(img.attr('src')==path+'enable-16.png'){
			img.attr('src',path+'disable-16.png');
		}else{
			img.attr('src',path+'enable-16.png');
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
					me.parents('.ui-tabs-panel:first').html(r.content).fadeIn();
				}
			})
			return false;
		});
	})
	$('a[title]').livequery(function(){
		$(this).attr('tabtitle',$(this).attr('title')).tooltip({showURL: false});
	});
	$('input[type=file]').live('change',function(){
		$(this).parents('form:first').submit();
		console.log($(this).parents('form:first'));
	});
//	$('table.sortable tbody').livequery(function(){
////		console.log('load!')
//		$(this).sortable({
//			revert: true,
//			update: function(element, ui) {
//				console.log($(this).sortable('serialize')); 
//			}
//		});
//		$('td, th').each(function(){ $(this).css('width',$(this).width()); });
//	});
})


