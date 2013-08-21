

function updateProgessClass(){
	this.$updateGrid = $('#update-grid');
	this.$stepGrid = $('#update-step-grid');
	
}

updateProgessClass.prototype.init = function(){
	var __this = this;

	
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
					jQuery("#update-grid").jqGrid('addRowData', i, row);
					i++;
				}
			

			});	

		}
	});
	
	return true;
}

updateProgessClass.prototype.activeStep = function(){
	return true;
}