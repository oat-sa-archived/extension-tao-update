var updateProgress = null;
updateProgress = new updateProgessClass(successMsg,successLink);
updateProgress.init();


function initUpdate(){
	var version = $( "input[name='update-to-version']:radio:checked" );
	updateProgress.setParams({'versionName':version.val()});
	updateProgress.activeSteps();

}	