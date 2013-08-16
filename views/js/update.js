/**
 * @author lionel
 */
var url = root_url + 'taoUpdate24/UpdateController/availableUpdates';



require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
	
	$.ajax({
		type: "POST",
		url: url,
		data: {},
		dataType: 'json',
		success: function(data){
	
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
				jQuery("#update-grid").jqGrid('addRowData', i, row);
				i++;
			}
		}
	});
							
});
