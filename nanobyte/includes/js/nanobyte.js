/**
 * @author michael
 */
var nanobyte = {
	ui: '',
	submitForm : function(me){
		if(this.initValidate()===true){
			$.ajax({
				url: me.parents('form').attr('action')+'/ajax',
				data: me.parents('form').serialize()+'&submit=true',
				dataType: 'json',
				type: 'post',
				success: function(r){
					if(r.callback){
						eval(r.callback+'("'+r.args+'")');
					}
					nanobyte.showInlineMessage(r.messages);
					if(r.content=='reload'){window.document.location.reload();}
					me.parents('tr:first').remove();
					if($('input[name=menuname]').length>0){
						$('input[name=menuname]').parent().html($('input[name=menuname]').val());
					}
				}
			});
		}else{
			return false;
		}
	},
	displayMessage : function(msg,t,b,h,w){
		if(!h){
			h = 'auto';
		}
		if(!w){
			w = 'auto';
		}
		$('body').append('<div id="dialog'+i+'" class="dialog">	<div id="mcont"><div class="messages hidden"></div></div>'+msg+'</div>');
		this.formatDialog();
		if (!b) {
			var	b = {
				'Ok': function(){
					$(this).dialog('close');
				}
			}
		}
		$('#dialog'+i).dialog({
			modal: true,
			show: 'puff',
			hide: 'puff',
			overlay: {
				opacity: 0.7,
				background: "black"
			},
			buttons: b,
			resizable: true,
			height: h,
			width: w,
			title: t,
			close: function(){
				i--;
				$(this).dialog('destroy').remove();
			}
		});
		i++;
	},
	initValidate : function(){
		var errors = false;
		$('.required').each(function(){
			if ($(this).children('input').val() == ''){
				$(this).children('input').focus().animate({backgroundColor:'#ffff80'}).keypress(function(){
					$(this).animate({
						backgroundColor: 'white'
					})
				});
				var msg = 'You must enter a '+$(this).children('label').text().replace(':','')+'!';
				nanobyte.showInlineMessage(nanobyte.createMessage('Error: '+msg,'error'));
				errors = true;
			}
		});
		if(!errors){
			return true;
		}else{
			return false;
		}
	},
	formatDialog : function(){
		$('.formheader').remove();
		$('.section').css('background-color',$('.ui-dialog').css('background-color'));
	},
	showInlineMessage : function(msg){
		if($('#mcont').parents('.ui-dialog:not(:hidden)').length>0){
			$('#mcont .messages').html(msg).slideDown('slow').pause(10000).slideUp('slow');
		}else{
			$('#messages').html(msg).removeClass('hidden').slideDown('slow').pause(10000).slideUp('slow');
		}
	},
	createMessage : function(msg, cls){
		var html = '<ul class="messages"><li class="'+cls+'">'+msg+'</li></ul>';
		return html;
	},
	initLoader : function(){
		$('#loading').dialog({
			autoOpen: false,
			modal: true,
			title: 'Loading, Please wait..',
			height: 50
		});
	},
	showLoader : function(){
		$('#loading').dialog('open');
	},
	hideLoader : function(){
		$('#loading').dialog('close');
	},
	deleteRows : function(rows){ // This will work on tables made by list.tpl
		var rowArray = rows.split('|');
		for(key in rowArray){
			if (rowArray[key] != "") {
				$('input[type=checkbox][value=' + rowArray[key] + ']').parents('tr:first').fadeOut('slow',function(){$(this).remove()});
			}
		}
	},
	changeGroup : function(rows){
		var rowArray = rows.split('|');
		for(key in rowArray){
			if (rowArray[key] != "") {
				$('input[type=checkbox][value=' + rowArray[key] + ']').parent().siblings(':eq(2)').text($('select[name=actions] option:selected').text());
			}
		}
	},
	addRow : function(jObj){
		var newRow = "<tr>";
		$('#content').find('.tablesorter:first').find('tr:last').children('td').each(function(){
			newRow += "<td class='"+$(this).attr('class')+"'></td>";
		})
		console.log(newRow);
		$('#content').find('.tablesorter:first').find('tr:last').after(newRow);
	},
	changeLink : function(args){
		var argsArray = args.split('|');
		var boolStr = argsArray[1] == 'enable' ? ['1','0'] : ['0','1'];
		var newSrc = $('#'+argsArray[2]).parents('td:first').siblings('.enabled').find('img').attr('src').replace(boolStr[0],boolStr[1]);
		$('#'+argsArray[2]).attr('href',$('#'+argsArray[2]).attr('href').replace(argsArray[0],argsArray[1])).children('img').attr('src',$('#'+argsArray[2]).children('img').attr('src').replace(argsArray[0],argsArray[1])).parents('td:first').siblings('.enabled').find('img').attr('src',newSrc);
		this.hideLoader();
		return false;
	},
	ajaxcall : function(event){
		var c = event.currentTarget.className.split(' ');
		$(this).addClass('active').parent().siblings('li').children('.active').removeClass('active');
		if (c[0] == 'action-link') {
			nanobyte.showLoader();
		}else{
			$('#content').fadeOut('slow');
		}
		$.ajax({
	  		url: $(this).attr('href')+'/ajax',
	  		cache: false,
			dataType: "json",
	  		success: function(r){
				if(r.callback){
					switch (r.callback) {
						case 'Dialog':
							nanobyte.displayMessage(r.content, r.title);
							nanobyte.hideLoader();
							break;
						default:
							eval(r.callback+'("'+r.args+'")');
							break;
					}
				}else{
					$("#content").html(r.content).prepend(r.tabs).fadeIn('slow');
				}
	
				nanobyte.formatDialog();
				if (r.messages){
					nanobyte.showInlineMessage(r.messages);
				}
	 		}
		});
		return false;
	}
}
