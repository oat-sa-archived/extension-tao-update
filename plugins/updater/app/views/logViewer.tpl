<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/reset.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/logViewer.css');?>" />
	<script type="text/javascript" src="<?= $this->assets('js/jquery-1.8.0.min.js'); ?>"></script>
	<script type="text/javascript" src="<?= $this->assets('js/logViewer.js'); ?>"></script>
	<script type="text/javascript" src="<?= $this->assets('js/updateProgress.js'); ?>"></script>
<head>
<title>Update in Progress</title>


</head>

<body>
	
	<div id="main">
	<h1>TAO Update Wizard</h1>
	<div id="error"></div>
		<div id="task">
		<h2>Update Steps</h2>
		
				<ul id="step"></ul>
		</div>

	
	
	

		<div id="content">
			
			<h2>Update in Progress</h2>
			<div id="log">
			
			</div>
			<script type="text/javascript">
				var root_url = "<?= $this->getData('ROOT_URL') ;?>";
				var img_url = "<?= $this->assets('img/') ;?>";
				
				var logViewer = new logViewerClass();
				logViewer.init();

				var step = new updateProgessClass();
				step.init();
				
			</script>
		</div>
	</div>
</body>
</html>