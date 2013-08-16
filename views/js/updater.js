function UpdaterClass(tableElementId) {
	var self = this;
	this.$grid = $('#'+tableElementId);
	this.init ();
	
}

UpdaterClass.prototype.init = function() {
	var self = this;
	var gridOptions = {
			datatype : "local",
			colNames : [ __('Version'), __('File')],
			colModel : [ {
				name : 'version',
				index : 'version',
				width : 75,
				align : "center",
				sortable : false
			}, {
				name : 'comment',
				index : 'comment',
				width : 450,
				sortable : false
			} ], 
			rowNum : 15,
			height : 'auto',
			autowidth : true,
			sortname : 'status',
			viewrecords : false,
			sortorder : "asc",
			caption : __("Available update")
	};

	//buil jqGrid:
	this.$grid.jqGrid(gridOptions);
}
UpdaterClass.prototype.addRowData = function(rowId, data) {
	this.$grid.jqGrid('addRowData', rowId, data);
	this.availableUpdates.push(data);
}

UpdaterClass.prototype.setRowData = function(rowId, data) {
	this.$grid.jqGrid('setRowData', rowId, data);
}
UpdaterClass.prototype.showUpdatesDetails = function() {
	var self = this;
	returnValue = [];
	
	if (this.updatable){
		$.ajax({
			type : "POST",
			async : false,
			url : this.getUpdateDetailsUrl,
			data : {},
			dataType : 'json',
			success : function(data) {
				returnValue = data;
				for (var i in data){
					self.addRowData(i, data[i]);
				}
			}
		});
	} else {
		this.$grid.html ('No Update available');
	}

	return returnValue;
}
			
