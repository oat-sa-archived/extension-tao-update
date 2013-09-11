<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<link rel="stylesheet" type="text/css" href="<?=TAOBASE_WWW?>css/reset.css" />
	<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>css/logViewer.css" />
	 <script src="<?=BASE_WWW ;?>js/jquery-1.8.0.min.js"></script>
	<script type="text/javascript" src="<?=BASE_WWW?>js/scriptsRunner.js"></script>
	<script type="text/javascript" src="<?=BASE_WWW?>js/logViewer.js"></script>
<head>
<title>Data Migration in Progress</title>


</head>

<body>
	
	<div id="main">
		<h1>TAO Data Migration Wizard</h1>
		<h2>Data Migration in Progress</h2>
		<div id="task">
		<h3>Steps</h3>
		
				<ul id="step"></ul>
		</div>
					
		<div id="content">
			<div id="updateMsg"></div>

			<div id="logContainer">
			<br/>
			<h3>Log</h3>
			<input type="button" value="Show Log" id="showLog" onclick="showLog()" />	
			<input type="button" value="Hide Log" id="hideLog" onclick="hideLog()" />	
			<br/>
			<div id="log" />
			</div>
			<script type="text/javascript">
				var logUrl = "<?= get_data('logUrl') ;?>";
				var root_url = "<?= ROOT_URL ;?>";
				var successLink = "<?= get_data('successLink') ;?>";
				var img_url = "<?=BASE_WWW?>img/";
				
				var logViewer = new logViewerClass(logUrl);
				logViewer.init();
				hideLog();
				
				var scriptRunner = new scriptsRunnerClass();
				scriptRunner.init();
				
				function showLog(){
					$('#log').show();
					$('#showLog').hide();
					$('#hideLog').show();
				}

				function hideLog(){
					$('#log').hide();
					$('#hideLog').hide();
					$('#showLog').show();
				}	
				
			</script>
		</div>
	</div>
</body>
</html>