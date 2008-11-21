/**
 * @author Michael
 */
$(document).ready(
	function () {
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
		function serialize()
		{
			var item1 = $('#groupContainer1').sortable("serialize") + '&';
			var item2 = $('#groupContainer2').sortable("serialize");
			var serial =  item1 + item2; 
			//$.post("gamerpanel", { data: serial}); 
			console.log('Serial:' + serial);
		};
	}
);