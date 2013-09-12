

function scriptsRunnerClass(){
	this.$step = $('#step');
	this.availableStepsUrl = root_url + 'taoUpdate/Data/provideSteps';
	this.$updateMsg = $('#updateMsg');
	this.availableSteps = [];
	this.stepIndex = 0;

}

scriptsRunnerClass.prototype.init = function(){
	var self = this;
	self.$updateMsg.hide();
	$.getJSON(self.availableStepsUrl, function(data) {
		$.each(data, function(key, val) {
			

			self.$step.append('<li id="update-step-action-' + val.action + '">' + val.name + '</li>');
			self.availableSteps.push(val);
		});
		
		self.updateProgress();
	});
}

scriptsRunnerClass.prototype.updateProgress = function() {
	//update progress bar
	
	if(this.stepIndex<this.availableSteps.length){

		this.run(this.availableSteps[this.stepIndex]);
	
		this.stepIndex++;
	}else{
		this.$updateMsg.show();
		$('<h3>Success</h3>').addClass('sucessMsg').appendTo(this.$updateMsg);

	}
}

scriptsRunnerClass.prototype.run = function(step){
	var self = this;
	var name = step.name;
	var action = step.action;
	var actionStatusTag = $('#update-step-action-' + action);
	var url = root_url + 'taoUpdate/Data/scriptRunner?script=' + action + '&extension=' + step.extension ;

	$('<img src="' + img_url + 'ajax-loader-small.gif" title="Running"/>').appendTo(actionStatusTag);
	
	$.getJSON(url, function(data) {
		
		actionStatusTag.html( '<li id="update-step-action-' + action + '">'+ name+ '<img src="'+img_url+'tick.png"/><li>');
		
		if(data.success == 1){
			self.updateProgress();
		}
		else {
			self.$updateMsg.show();
			actionStatusTag.html( '<li id="update-step-action-' + action + '">'+ name+ '<img src="'+img_url+'failed.png"/><li>');
			var error = self.$updateMsg;
			$('<h3>Error</h3>').addClass('errorMsg').appendTo(error);
			$.each(data.failed, function(key, val) {
				$('<p>' + val +'<p>').addClass('errorMsg').appendTo(error);
				
				
			});
			$('<br/>').appendTo(error);
		}
	});
	
}