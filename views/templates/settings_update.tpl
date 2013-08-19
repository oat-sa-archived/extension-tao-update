<?if(get_data('message')):?>
	<div id="info-box" class="ui-corner-all auto-highlight auto-hide">
		<?=get_data('message')?>
	</div>
<?endif?>
<? if (get_data('updatable')) include('update.tpl'); ?>

<?include(TAO_TPL_PATH.'footer.tpl')?>