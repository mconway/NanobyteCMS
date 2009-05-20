/*
 * @author Michael
 */
$(document).ready(
	function () {
		$('#iconmenu ul').addClass('jcarousel-skin-tango').jcarousel();
		$('#iconmenu ul li a').click(nanobyte.ajaxcall);
		$('.action-link').livequery(function(){
			$(this).click(nanobyte.ajaxcall);
		});
		$('.action-link-tab').live('click',function(){
			$(this).parents('.tabs:first').tabs('add',$(this).attr('href'),$(this).attr('title'));
//			nanobyte.formatContents(false);
			return false;
		});
		$('.groupWrapper').livequery(function(){
			$(this).sortable({
				items: '.groupItem',
				handle: '.itemHeader',
				//stop: function(){ $(this).sortable("refresh"); alert($(this).sortable("serialize")); },
				update: function(element, ui) {
					serialize(); 
				},
				connectWith: ['.groupWrapper'], 
				revert: true,
				dropOnEmpty: true
			});
		})

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
