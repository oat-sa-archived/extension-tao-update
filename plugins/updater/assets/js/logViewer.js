

function logViewerClass(){
	this.url = root_url + 'log/update.log';
}

logViewerClass.prototype.init= function(){
	var self = this;
	
    $.ajax({
        url: self.url,
        dataType: 'text',
        success: function(data) {
        	$("#log").html('');
        	var lines = data.split("\n");
        	$.each(lines, function(n, elem) {
        		var formatedLine = self.format(elem) ;
        		$("#log").append(formatedLine);
        	});
        	//console.log('ttot');
        	setTimeout(function(){self.init()}, 30000); // refresh every 30 seconds
            
        }
    })
	
}


logViewerClass.prototype.format= function(line){
	var self = this;
	var logLevelArray = ['debug','info','error','trace'];
	var returnValue = line;
	jQuery.each(logLevelArray, function(){
		formatedLine = self.addDiv(line,this);
		
		if(formatedLine != false){
			returnValue = formatedLine;
			//console.log(returnValue);
			return;
		}
	});
	return returnValue;
	
}

logViewerClass.prototype.addDiv= function(line,logLevel){
	var upLogLevel = logLevel.toUpperCase();
	
	if(line.indexOf(upLogLevel) > 0){
		// remove FILE and LINE
	
		var formated =  '<div id="' + logLevel + '">' + line + '</div>';
		 return formated;
	}
	else {
		return false;
	}
	
}