/**
 * @author lionel
 */



	
	$.ajax({
		type: "POST",
		url: root_url + 'taoUpdate/Update/availableUpdates',
		data: {},
		dataType: 'json',
		success: function(data){
			require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
				jQuery("#update-grid").jqGrid({
					
					datatype: "local", 
					hidegrid : false,
					colNames: [ __('Version'), __('File'),], 
					colModel: [ 
						{name: 'version', index: 'version', sortable: false},
						{name: 'file', index: 'file', align: 'center', sortable: false},
					], 
			
					rowNum:10,
					height: 'auto', 
					width:'auto', 
			
					caption: __("Available Update"),
								         
				});
				
				var i = 0;
				for (var update in data){
					var row = data[update];
					console.log(row);
					jQuery("#update-grid").jqGrid('addRowData', i, row);
					i++;
				}
			

			});	

		}
	});
	

							



function back(){
	$("#init-update").show();
	$("#update-inProgress").hide();
}

function initUpdate(){
	$("#init-update").hide();
	
	
	
	$.getJSON(root_url + 'taoUpdate/Update/progress', function(data) {
		var step = '<ul>'
		$.each(data, function(key, val) {
			step += '<li>' + key + ' <strong>' + val + '</strong></li>';
			//console.log( key + '|' + val );
		});
		step +='</ul>'
		$("#update-step").html(step);
		
		require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
			jQuery("#update-step-grid").jqGrid({
				datatype: "local", 
				hidegrid : false,
				colNames: [ __('Step'), __('Name'),], 
				colModel: [ 
							{name: 'step', index: 'step', sortable: false},
							{name: 'data', index: 'data', align: 'center', width: 200, sortable: false},
						], 
		
				rowNum:10,
				height: 'auto', 
				width:'auto', 
		
				caption: __("Update in progress"),
							         
			});
			
			var i = 0;

				$.each(data, function(key, val) {
					var row = {'step':key,'data':val};
					
					jQuery("#update-step-grid").jqGrid('addRowData', i, row);
					i++;
					
				});
			
			});
		
		
	});


}	