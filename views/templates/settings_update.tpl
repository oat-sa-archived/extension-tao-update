<link rel="stylesheet" href="<?=BASE_WWW?>css/settings.css" type="text/css" />
<?if(get_data('message')):?>
	<div id="info-box" class="ui-corner-all auto-highlight auto-hide">
		<?=get_data('message')?>
	</div>
<?endif?>
<?include('update.tpl');?>

<?include(TAO_TPL_PATH.'footer.tpl')?>