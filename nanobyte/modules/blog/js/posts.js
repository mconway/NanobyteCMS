/**
 * @author michael
 */
$(document).ready(function(){
	$('#postsbydate').html('').datepicker({
		onSelect: function(date) { 
			$('.tabs').tabs('select',5);
	        // $(nanobyte.ui.panel).load('user/register/ajax');
			$(nanobyte.ui.panel).html('The Chosen date is: '+date+' This module is not fully functional!');
	        return false;
   		},
		monthNamesShort:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']	
	})
})
