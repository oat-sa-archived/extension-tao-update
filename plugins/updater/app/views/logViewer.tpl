<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/reset.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?= $this->assets('css/logViewer.css');?>" />
	<script type="text/javascript" src="<?= $this->assets('js/jquery-1.8.0.min.js'); ?>"></script>
	<script type="text/javascript" src="<?= $this->assets('js/logViewer.js'); ?>"></script>
	<script type="text/javascript" src="<?= $this->assets('js/scriptsRunner.js'); ?>"></script>
<head>
<title>Update in Progress</title>


</head>

<body>
	
	<div id="main">
		<h1>TAO Update Wizard</h1>
		<h2>Update in Progress</h2>
		<div id="task">
		<h3>Steps</h3>
		
				<ul id="step"></ul>
		</div>
					
		<div id="content">
			<div id="updateMsg"></div>

			<div id="logContainer">
			<br/>
			<h3>Log</h3>
			<input type="button" value="Show" id="showLog" onclick="showLog()" />	
			<input type="button" value="Hide" id="hideLog" onclick="hideLog()" />	
			<br/>
			<div id="log" />
			</div>
			<script type="text/javascript">
				var root_url = "<?= $this->getData('ROOT_URL') ;?>";
				var img_url = "<?= $this->assets('img/') ;?>";
				
				var logViewer = new logViewerClass();
				logViewer.init();
				$('#log').hide();
				
				var scriptRunner = new scriptsRunnerClass();
				scriptRunner.init();
				
				function showLog(){
					$('#log').show();
				}

				function hideLog(){
					$('#log').hide();
				}	
				
			</script>
		</div>
	</div>
</body>
</html>