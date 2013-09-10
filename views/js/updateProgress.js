

function updateProgessClass(){
	this.$updateGrid = $('#update-grid');
	this.$stepGrid = $('#update-step-grid');
	this.moduleUrl = root_url + 'taoUpdate/Update/'
	this.availableUpdatesUrl = this.moduleUrl + 'availableUpdates';
	this.availableStepsUrl = this.moduleUrl + 'getUpdateSteps'
	
	this.img_root = root_url + "/taoUpdate/views/img/";
	this.availableSteps = [];
	this.stepIndex = 0;
	
}


updateProgessClass.prototype.init = function(){
	var self = this;
	
	$.ajax({
		type: "POST",
		url: self.availableUpdatesUrl,
		data: {},
		dataType: 'json',
		success: function(data){
			require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
					self.$updateGrid.jqGrid({
						
					datatype: "local", 
					hidegrid : false,
					colNames: [ __('Version'), __('File'),''], 
					colModel: [ 
						{name: 'version', index: 'version', sortable: false},
						{name: 'file', index: 'file', align: 'center', sortable: false},
						{name:'actions',index:'actions', align:"center", width: 30, sortable: false}
						
					], 
			
					rowNum:10,
					height: 'auto', 
					width:'auto', 
			
					caption: __("Available Update"),
								         
				});
					
				var i = 0;
				for (var update in data){
					var row = data[update];
					row.actions = '<input type="radio" name="update-to-version" value="' + update + '" />'
					self.addRow(self.$updateGrid, i, row);
					i++;
				}
			

			});	

		}
	});
	
	return true;
}

updateProgessClass.prototype.activeSteps = function(){
	this.$stepGrid.empty();
	$("#init-update").hide();

	var self = this;
	
	$.getJSON(self.availableStepsUrl, function(data) {
		
		require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
			self.$stepGrid.jqGrid({
				datatype: "local", 
				hidegrid : false,
				colNames: [ __('Step'), __('Name'),__('Status'),], 
				colModel: [ 
							{name: 'step', index: 'step', sortable: false ,align: 'center'},
							{name: 'name', index: 'name', width: 250, sortable: false},
							{name: 'status' , index:'status', sortable: false, align: 'center'},
						], 
		
				rowNum:10,
				height: 'auto', 
				width:'auto', 
		
				caption: __("Update in progress"),
							         
			});
			var i = 0;

				$.each(data, function(key, val) {
					var divStatus = '<div id="update-step-action-' + val.action + '">' 
					var row = {'step':i+1,'name':val.name,'status': divStatus + val.status + '</div>'};
					self.addRow(self.$stepGrid, i, row);
					self.availableSteps.push(val);
					i++;
					
				});
			
			
			});
		self.updateProgress();
		
	});

	return true;
}


updateProgessClass.prototype.updateProgress = function() {

	//update progress bar
	
	if(this.stepIndex<this.availableSteps.length){

		this.run(this.availableSteps[this.stepIndex]);
	
		this.stepIndex++;
	}else{
		$("#init-update").show();
		this.$stepGrid.show();
		$("#update-table-container").hide();

		$('#update-button-container').hide();
		var info = $('#update-info').removeClass('ui-state-error').addClass('ui-state-highlight');
		info.html('<img src="'+this.img_root+'tick.png"/>' +   __('New Version have been downloaded and will now be extracted, we will not replace current installation') );	

	}
}

updateProgessClass.prototype.setParams = function(data){
	this.params = data;
}
updateProgessClass.prototype.addParams = function(action){
	
	if(this.params != null && action !=null){
		var truc = {'action':action,'versionName':this.params.versionName};
		var encoded = jQuery.param(truc);
		return '?'  + encoded;
	}
	return;
}

updateProgessClass.prototype.run = function(step){
	var self = this;

	var actionStatusTag = $('#update-step-action-' + step.action);
	var url = self.moduleUrl + 'run'  + this.addParams(step.action) ;

	
	
	actionStatusTag.html( __('In progress...'));
	$('<img src="'+ self.img_root + 'ajax-loader-small.gif' +'" title="'+ __('In progress...') +'"/>').css('top', 2).appendTo(actionStatusTag);

	$.getJSON(url, function(data) {

		if(data.success == 1){
			actionStatusTag.html( __('Complete') + ' <img src="'+self.img_root+'tick.png"/>');	
			self.updateProgress();
		}
		else {

			$("#init-update").show();

			
			actionStatusTag.html( __('Fail') + ' <img src="'+self.img_root+'failed.png"/>');
			var error = $('#update-info').removeClass('ui-state-highlight').addClass('ui-state-error');
			error.empty();
			$.each(data.failed, function(key, val) {
				$('<p>' + val +'<p>').appendTo(error);
				
				
			});

		}

	});
		
}

updateProgessClass.prototype.addRow = function(grid, rowId, data) {
	grid.jqGrid('addRowData', rowId, data);
}

