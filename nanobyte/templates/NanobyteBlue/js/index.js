/**
 * @author Michael
 */
var i = 0;

$(document).ready(function(){
//	nanobyte.initLoader();
	
	$.preloadImages('templates/NanobyteBlue/images/list-info.png','templates/NanobyteBlue/images/list-error.png');
	
	$('#loginform').dialog({
		autoOpen : false,
		title: 'Please Log In',
		height: 220,
		width: 400,
		modal: true,
		closeOnEscape: false,
		buttons: {
			'Log In' : function(){nanobyte.submitForm($('#loginform form'),$(this));}
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

	$('textarea#ckeditor').livequery(function(){
		if(CKEDITOR && CKEDITOR.instances.ckeditor){
			CKEDITOR.instances.ckeditor.destroy();
		}
		CKEDITOR.replace('ckeditor');
	})

	$('input[type=submit]').live('click',function(){
		nanobyte.submitForm($(this).parents('form'),$(this));
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
	$('#profile-tabs').tabs();
	$('.tabs').livequery(function(){
		$(this).tabs({
			ajaxOptions: { 
				type: 'POST',
				data: 'actions=/ajax'
			} ,
//			select : function(event, ui){
//				nanobyte.lastUI = nanobyte.ui;
//				nanobyte.ui = ui;
//			},
			fx : {
				opacity : 'toggle'
			},
			add: function(e, ui) {
		        $(this).tabs('select', '#' + ui.panel.id);
				$('a[href=#'+ui.panel.id+']').after('<a class="tabClose" id="tab_'+ui.index+'"></a>');
		    },
			spinner: '<img src="templates/NanobyteBlue/images/tab_loader.gif"/><i> Loading...</i>',
			load: function(e, ui){
				nanobyte.lastUI = nanobyte.ui;
				nanobyte.ui = ui;
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
		var me = $(this);
		var myID = me.attr('id');
		$.ajaxFileUpload({
			url:me.parents('form:first').attr('action')+'/image/ajax',
            secureuri:false,
            fileElementId:'image',
            dataType: 'json',
            success: function (r, status){
				if(!r.callback){
					nanobyte.addThumbnail(myID,r);
				}else{
					eval(r.callback+'("'+r.args.thumb+'")');
				}
				
			},
			error: function(e,t,et){
				console.log(me.parents('form:first').attr('action')+'/image/ajax');
				console.log(e,t,et);
			}
		});
	})
	$('#menu-accordion').accordion({header: 'h3', navigation: true});
	$('#show-file-dialog').live('click',function(){
		var files = '';
		var filelist = $('#imagelist').val().split(';');
		$.each(filelist,function(){
			var tmp = this.split('|');
			if(tmp[0]){
				files += '<a id="file-link" title="Click to remove this file" href="'+this+'">'+tmp[1].replace('files/','')+'</a><br />';
			}
		});
		nanobyte.displayMessage(files,'Files Uploaded');
	});
	$('#file-link').live('click',function(){
		$('#imagelist').val($('#imagelist').val().replace($(this).attr('href')+';',''));
		$(this).remove();
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
	$('form input').livequery(function(){
		$(this).keypress(function(e){
			if(e.which==13){
				nanobyte.submitForm($(this).parents('form:first'),$(this).parents('form:first').find('input[type-submit]'));
			}
		})
	})
	$('a.thickbox').livequery(function(){
		tb_init('a.thickbox');
	})
})
function changeAvatar(r){
	$('#profile-info').find('img#avatar').attr('src',r);
}

