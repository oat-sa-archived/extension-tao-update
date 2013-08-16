<link rel="stylesheet" href="<?=BASE_WWW?>css/settings.css" type="text/css" />
<script type="text/javascript">
$(function () {
    $("#list").jqGrid({
        url: "http://tao24.localdomain/taoUpdate24/UpdateController/availableUpdatesXml",
        datatype: "xml",
        mtype: "GET",
		colNames : [ __('Version'), __('File')],
		colModel : [ {
			name : 'version',
			index : 'version',
			width : 75,
			align : "center",
			sortable : false
		}, {
			name : 'file',
			index : 'file',
			width : 450,
			sortable : false
		} ], 
        pager: "#pager",
        rowNum: 10,
        rowList: [10, 20, 30],
        sortname: "invid",
        sortorder: "desc",
        viewrecords: true,
        gridview: true,
        autoencode: true,
        caption: "My first grid"
    }); 
}); 
</script>

<div id="compilation-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=__("TAO Update")?>
</div>

<div class="main-container">
	<div id="update-container" class="ui-widget-content ui-corner-bottom">
	 	<div id="update-info" class="ext-home-container ui-state-highlight">
			<?= __("New version of TAO is availlable, you may launch upgrade procedure")?>
			<div id="update-warning">
	        	<strong><?=__("Warning:") . __(" make sure to back up your data before launching upgrade."); ?>
	        	</strong> 
	        </div>
		</div>
		<div>
		<p>Operation</p>
			<ul>
				<li>lock the platform</li>
				<li>create backups</li>
				<li>download last version if possible</li>
				<li>deploy last version</li>
			</ul>
		</div>
		<?var_dump(get_data('availabeUpdate'));?>
		<?if(get_data('availabeUpdate')):?>
				
			<div id="available_update-container" class="ext-home-container ui-state-highlight">
				<span><?=__('Updates are available. Click on update to update your version of TAO')?></span>
					<br/><br/>
					<div id="update-table-container">
						<ul>
						<?foreach(get_data('availabeUpdate') as $update) :?>
						<li><a href="REPO/<?=$update['file'];?>"><?=$update['version'];?></a></li>
						<?endforeach;?>
						</ul>
						
	
					</div>
			</div>


			<div id="update-button-container">
	        	<input type="button" value="<?=__("Launch update")?>" id="compileButton"/>
	
	        </div>
		<?else:?>
			<div id="no_update-container" class="ext-home-container ui-state-highlight">
				<span><?=__('No update available')?></span>
			</div>
		<?endif;?>


		        		

	</div>
</div>

