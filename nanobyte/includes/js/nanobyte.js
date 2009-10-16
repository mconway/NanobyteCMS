/**
 * @author michael
 */
var nanobyte = {
	ui: {index: 0},
	lastUI: {index: 0},
	submitForm : function(form,me){
		if(this.initValidate(form)===true){
			if (CKEDITOR && CKEDITOR.instances.ckeditor) {
				form.find('textarea').val(CKEDITOR.instances.ckeditor.getData());			
			}
			var data = form.serialize()+'&'+me.attr('name')+'=true';
//			var data = form.serialize()+'&submit=true';

			$.ajax({
				url: form.attr('action')+'/ajax',
				data: data,
				dataType: 'json',
				type: 'post',
				success: function(r){
					if(r.callback){
						if(r.callback=='reload'){
							setTimeout('window.document.location.reload()',2000);
						}else if(r.callback=='reset'){
							form.get(0).reset();
						}else{
							eval(r.callback+'("'+r.args+'")');
						}
					}else if(!nanobyte.empty(r.content)){
						$("#content").html(r.content).prepend(r.tabs).fadeIn('slow');
					}
					nanobyte.showInlineMessage(r.messages);
					$(this).dialog('close');
					
					//this is a temp fix for installer
					if(typeof r.block_title == 'string' && typeof r.block_body == 'string') {
						updateBlock(r.block_title,r.block_body);
					} 
					
					return true;
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
		this.formatContents(true);
		if (!b) {
			var	b = {
				'Ok': function(){
					if($('.ui-dialog').find('form').length > 0){
						nanobyte.submitForm($('.ui-dialog').find('form'),$(this));
					}else{
						$(this).dialog('close');
					}
				}
			}
		}
		$('#dialog'+i).dialog({
			modal: true,
//			show: 'puff',
//			hide: 'puff',
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
	initValidate : function(form){
		var errors = false;
		form.find('.required').each(function(){
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
	formatContents : function(dialog){
		$('.formheader').remove();
		$('.section').css('background-color',$('.ui-dialog').css('background-color'));
		if(dialog){
			$('.ui-dialog').find('.formbutton').remove();
		}
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
	showLoader : function(){
		$('#loading').css('height',$('#container').height()+5);
		$('#loading').css('display','block');
	},
	hideLoader : function(){
		$('#loading').css('display','none');
	},
	deleteRows : function(rows){ // This will work on tables made by list.tpl with checkboxes only
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
				$('input[type=checkbox][value=' + rowArray[key] + ']').parent().siblings(':eq(3)').text($('select[name=actions] option:selected').text());
			}
		}
	},
	addRow : function(jObj){
		var newRow = "<tr>";
		$('#content').find('.tablesorter:first').find('tr:last').children('td').each(function(){
			newRow += "<td class='"+$(this).attr('class')+"'></td>";
		})
//		console.log(newRow);
		$('#content').find('.tablesorter:first').find('tr:last').after(newRow);
	},
	changeLink : function(args){
		var argsArray = args.split('|');
		var boolStr = argsArray[1] == 'enable' ? ['1','0'] : ['0','1'];
		var newSrc = $('#'+argsArray[2]).parents('td:first').siblings('.'+argsArray[3]).find('img').attr('src').replace(boolStr[0],boolStr[1]);
		$('#'+argsArray[2]).attr('href',$('#'+argsArray[2]).attr('href').replace(argsArray[0],argsArray[1])).children('img').attr('src',$('#'+argsArray[2]).children('img').attr('src').replace(argsArray[0],argsArray[1])).parents('td:first').siblings('.'+argsArray[3]).find('img').attr('src',newSrc);
		this.hideLoader();
		return false;
	},
	ajaxcall : function(event){
		var classes = event.currentTarget.className.split(' ');
		var noloader = false;
//		window.location.hash = $(this).attr('href').replace();
		$(this).addClass('active').parent().siblings('li').children('.active').removeClass('active');
		$.each(classes,function(i,v){
			if(v=='noloader'){
				noloader=true;
			}
		})
		if(!noloader){
			nanobyte.showLoader();
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
							break;
						case 'Redirect':
							window.location = r.args;
							break;
						default:
							eval(r.callback+'("'+r.args+'")');
							break;
					}
					nanobyte.hideLoader();
				}else{
					$("#content").html(r.content).prepend(r.tabs).fadeIn('slow');
					nanobyte.hideLoader();
				}
				nanobyte.formatContents(false);
				if (r.messages && r.messages!=false){
					nanobyte.showInlineMessage(r.messages);
				}
	 		}
		});
		return false;
	},
	redirect : function (location){
		window.location = location;
	},
	empty: function(v){
		if(v==null||v==""||typeof(v)=="undefined"){
			return true;
		}else{
			false;
		}
	},
	closeParentTab : function(e){
		$('a[href=#'+$(e).parents('.ui-tabs-panel:first').attr('id')+']').next('a.tabClose').click();
	},
	moveRow : function(args){
		var argsArray = args.split('|');
		var id = argsArray[0];
		var func = argsArray[1];
		var weight = parseInt($('#'+id).find('td.weight').text());
		if(func == 'down'){
			var next = $('#'+id).next('tr').length > 0 ? '#'+$('#'+id).next('tr').attr('id'): '';
			if(next !='' && weight+1 > $(next).find('td.weight').text()){
				$('#'+id).clone().insertAfter(next);
				$('#'+id).remove();
			}
			$('#'+id).find('td.weight').text(parseInt($('#'+id).find('td.weight').text())+1);
		}else{
			var prev = $('#'+id).prev('tr').length > 0 ? '#'+$('#'+id).prev('tr').attr('id'): '';
			if(prev != '' && weight-1 < $(prev).find('td.weight').text()){
				$('#'+id).clone().insertBefore(prev);
				$(prev).next().remove();
			}
			$('#'+id).find('td.weight').text(parseInt($('#'+id).find('td.weight').text())-1);
		}
		return false;
	},
	addThumbnail : function(myID,r){
		if($('#thumbnail').length == 0){
			$('#'+myID).after("<div id='thumbnail'><img src='"+r.args.thumb+"'/><input type='hidden' name='imagelist' id='imagelist'/></div>");
			$('#thumbnail').append("<br/><a id='show-file-dialog'>Show all files </a>(<span id='file-count'>1</span>)");
		}else{
			$('#thumbnail img').attr('src',r.args.thumb);
			$('#file-count').text(parseInt($('#file-count').text())+1);
		}
//		nanobyte.showInlineMessage(r.messages);
		$('#imagelist').val($('#imagelist').val()+r.args.thumb+'|'+r.args.orig+';');
	}
}


jQuery.preloadImages = function()
{
  for(var i = 0; i<arguments.length; i++)
  {
    jQuery("<img>").attr("src", arguments[i]);
  }
}