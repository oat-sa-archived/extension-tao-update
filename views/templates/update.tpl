<script type="text/javascript">

var successMsg = '<?= get_data("successMsg") ;?>';
var successLink ='<?= get_data("successUrl") ;?>';
var availlableUpdates = JSON.parse('<?= get_data("updatesaAvailable") ;?>');

</script>
<script type="text/javascript" src="<?=BASE_WWW?>js/updateProgress.js"></script>
<script type="text/javascript" src="<?=BASE_WWW?>js/update.js"></script>
<div id="compilation-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=__("TAO Update")?>
</div>

<div class="main-container">
	<div id="update-container" class="ui-widget-content ui-corner-bottom">
	 	<div id="init-update">
	 	<?if(get_data('isUpdateAvailable')):?>
		 	<div id="update-info" class="ext-home-container ui-state-highlight">
	
				<?= __("New version of TAO is availlable, you may launch upgrade procedure that will perform following step.")?>
				<ul>
						<li>lock the platform</li>
						<li>download last version if possible</li>
						<li>deploy last version</li>
				</ul>
				<strong><?= __("Please be aware that during the update, all actual running deliveries will not be able to be resumed and will be set to status abandoned") ?></strong>
				
				<strong><?= __("We strongly recommand you to backup your root folder and database before launching the update")?></strong>
				
			</div>
			<br/>


			<br/>
			<?if (get_data('isDesignModeEnabled')):?>
			
			<div id="update-table-step-container">

	        	<table id="update-step-grid" />		
			</div>
			<br/>
			<div id="update-table-container">
			<?=__('Select the update you want to install')?>
			<br/>
			
				<table id="update-grid" />
			</div>

	        <?else :?>
	        <div id="update-error">
	        	<img src="<?= ROOT_URL ?>tao/views/img/warning.png"
				alt="warning" class="embedWarning" />
	        	<strong><?=__("Warning: ") . __("TAO should be in design mode to perform any major update."); ?>
	        	</strong> <?= __("Go to TAO optimizer to switch all classes in Design Mode.");?>
	        </div>
	        <?endif;?>
	        </div>
	        <br/>
	       
	        

			
			<div id="update-button-container">
	        	<input type="button" value="<?=__("Launch update")?>" id="updateButton" onclick="initUpdate()" />
	
	        </div>


	</div>
	<?else :?>
	
	 	<div id="update-info" class="ext-home-container ui-state-highlight">
			<?= __("Any new version of TAO is available")?>

		</div>
	
	<?endif;?>
		        		

	
</div>

