

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
					row.actions = '<input type="radio" name="update-to-version" id="' + update + '" />'
					self.addRow(self.$updateGrid, i, row);
					i++;
				}
			

			});	

		}
	});
	
	return true;
}

updateProgessClass.prototype.back = function(){
	$("#init-update").show();
	$("#update-inProgress").hide();
	
}

updateProgessClass.prototype.activeSteps = function(){
	$("#init-update").hide();
	$("#update-inProgress").show();
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

		//end
	}
}


updateProgessClass.prototype.run = function(step){
	var self = this;
	var action = step.action;
	var actionStatusTag = $('#update-step-action-' + action);


	var url = self.moduleUrl + action;
	console.log(url);
	actionStatusTag.html( __('In progress...'));
	$('<img src="'+ self.img_root + 'ajax-loader-small.gif' +'" title="'+ __('In progress...') +'"/>').css('top', 2).appendTo(actionStatusTag);

	$.getJSON(url, function(data) {

		console.log(data);

		actionStatusTag.html( __('Complete') + ' <img src="'+self.img_root+'tick.png"/>');
		self.updateProgress();

	});
		
}

updateProgessClass.prototype.addRow = function(grid, rowId, data) {
	grid.jqGrid('addRowData', rowId, data);
}

