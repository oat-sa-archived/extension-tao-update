<header class="section-header flex-container-full">
    <h2><?=__("TAO Update")?></h2>
</header>
<?php if(!get_data('isDesignModeEnabled')):?>
    <div class="feedback-warning">
        <span class="icon-warning"></span>
        <p><?=__('The platform can only be updated in design mode (can be changed in the TAO Optimizer)')?></p>
    </div>
<?php else : ?>
<div  class="main-container flex-container-full">
    <div>
        <p><?=__('Select the version of TAO to install.')?></p>
        <p><strong><?= __("We strongly recommand you to backup your root folder and database before launching the update.")?></strong></p>
    </div>

    <div id="tao-update-container" class="hidden" data-success="<?=get_data('installLink')?>">
        <em><?=__('There is no udpate available.')?></em>
    </div>
</div>
<?php endif; ?>
