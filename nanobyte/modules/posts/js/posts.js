/**
 * @author michael
 */
$(document).ready(function(){
	$('#postsbydate').html('').datepicker({
		onSelect: function(date) { 
       		 alert("The chosen date is " + date + '\n We Apologize but this block is not yet fully-functional'); 
   		},
		monthNamesShort:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
		
	})
})
