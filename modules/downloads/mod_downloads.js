$(document).ready(function(){
	$('input[type=file]#file').live('change',function(){
		var me = $(this);
		var myID = me.attr('id');
		$.ajaxFileUpload({
			url:me.parents('form:first').attr('action')+'/ajax',
            secureuri:false,
            fileElementId:'file',
            dataType: 'json',
            success: function (r, status){
				if(r.callback){
					eval(r.callback+'("'+r.args+'")');
				}
				
			},
			error: function(e,t,et){
				console.log(me.parents('form:first').attr('action'));
				console.log(e,t,et);
			}
		});
	})
})
function updateFile(fileName){
	$('#filename').val(fileName);
}
