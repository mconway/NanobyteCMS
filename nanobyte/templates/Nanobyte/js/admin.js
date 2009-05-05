/*
 * @author Michael
 */
$(document).ready(
	function () {
		$('#iconmenu a').click(ajaxcall);
		$('.action-link').livequery(function(){
			$(this).click(ajaxcall);
		});
		$("#tabs").livequery(function(){
			$(this).tabs({
				ajaxOptions: { 
					type: 'POST',
					data: 'actions=/ajax',
				} 
			});
		});
		$('a.closeEl').bind('click', function(){
			if ($(this).parents(".itemHeader").siblings(".itemContent").css('display') == 'none') {
				$(this).parents(".itemHeader").siblings(".itemContent").slideDown(300);
				$(this).html('[-]');
			} else {
				$(this).parents(".itemHeader").siblings(".itemContent").slideUp(300);
				$(this).html('[+]');
			}
			return false;
		});
		$('#groupContainer1').sortable({
			items: '.groupItem',
			handle: '.itemHeader',
			//stop: function(){ $(this).sortable("refresh"); alert($(this).sortable("serialize")); },
			update: function(element, ui) {
				serialize(); 
			},
			connectWith: ['#groupContainer2'], 
			revert: true,
			dropOnEmpty: true
		});
		$('#groupContainer2').sortable({
			items: '.groupItem',
			handle: '.itemHeader',
			update: function(element, ui) {
             serialize(); 
			},
			connectWith: ['#groupContainer1'], 
			revert: true,
			dropOnEmpty: true
		});
		$('#addtype').livequery(function(){
			$(this).click(function(){
				$('#loading').dialog('open');
				$.ajax({
					url: $(this).attr('href')+'/ajax',
					type: 'get',
					dataType: 'json',
					success: function(html){
						displayMessage('Add Content Type', html.content,200,400);
						$('#loading').dialog('close');
					}
				});
				return false;
			});
		});
		function serialize()
		{
			var item1 = $('#groupContainer1').sortable("serialize") + '&';
			var item2 = $('#groupContainer2').sortable("serialize");
			var serial =  item1 + item2; 
			console.log('Serial:' + serial);
		};
	}
);
var ajaxcall = function (){
	$(this).addClass('active').siblings('.active').removeClass('active');
	$('#loading').dialog("open");
	$.ajax({
  		url: $(this).attr('href')+'/ajax',
  		cache: false,
		dataType: "json",
  		success: function(data){
    		$("#content").html(data.content).prepend(data.tabs);
			if (data.messages){
				$('#messages').html(data.messages);
				$('#messages').dialog('open');
			}
 		},
		complete: function(){
			$('#loading').dialog("close");
		} 
	});
	return false;
}