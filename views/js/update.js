var successMsg = "<?= get_data('successMsg') ;?> <a href=\"<?= get_data('successUrl') ;?>\">"  + __('Click here to proceed'); + '</a>'
var updateProgress = null;
updateProgress = new updateProgessClass(successMsg);
updateProgress.init();


function initUpdate(){
	var version = $( "input[name='update-to-version']:radio:checked" );
	updateProgress.setParams({'versionName':version.val()});
	updateProgress.activeSteps();

}	