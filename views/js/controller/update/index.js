/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'helpers',
    'async',
    'tpl!taoUpdate/controller/update/version'//,
    //'ui/progressbar'
],
function($, _, __, helpers, async, versionTpl){

    /**
     * Get the list of available updates
     * @returns {jQuery.Deferred} to chain with done, fail or always
     */
    var loadUpdateData = function loadUpdateData(){
        return $.getJSON(helpers._url('available', 'Update', 'taoUpdate'));
    };

    /**
     * Build the list of available updates, using the version template.
     *
     * @param {jQueryElement} $container - the element to attach the updates
     * @param {Object} updates - the update list as sent by the server
     */
    var buildUpdateList = function buildUpdateList($container, updates){
        if(_.size(updates) > 0){
            $container.empty();
            _.forEach(updates, function(update){
                $container.append(versionTpl(update));
            });
        }
        $container.removeClass('hidden');
    };

    /**
     * Launch the upgrade for a selected release
     * @param {String} version - the selected version
     * @param {String} successUrl - the URL to redirect the user in case of success
     * @param {jQueryElement} $upgrading - the element that contains the upgrading elements (message, progressbar, etc.)
     */
    var launchUpgrade = function launchUpgrade(version, successUrl, $upgrading){

        var $msg = $('.message', $upgrading);
        var $progressBar = $('.status', $upgrading);

        /**
         * Running each step of the upgrade, mean calling the server with different actions names and checking the result
         * @param {String} action - the name of the action to run
         * @param {String} description - the message to display to the user, during the step processing
         * @param {Number} percent - the percent to update the progressbar once done
         * @param {Function} cb - err/callback with 1st param as the error or null.
         */
        var runStep = function runStep(action, description, percent, cb){
            var url  = helpers._url('run', 'Update', 'taoUpdate');
            var data = {
                version : version,
                action  : action
            };

            $msg.text(description);

            $.getJSON(url, data).done(function(response){
                if(response && response.success){
                    cb(null);
                    $progressBar.progressbar('update', percent);
                } else {
                    cb(new Error('Fail to run step ' + action + ' : ' + response.error));
                }
            }).fail(function(){
                cb(new Error('Fail to call step ' + action));
            });
        };

        $upgrading.removeClass('hidden');

        //init the progressbar
        $progressBar.progressbar();
        $progressBar.progressbar('update', 5);

        //run each steps one by one
        async.series([
            _.partial(runStep, 'downloadRelease', __('Download the new release'), 40),
            _.partial(runStep, 'deploy', __('Extract and deploy the new release'), 80),
            _.partial(runStep, 'lock', __('Lock the platform'), 100),
        ], function(err, results){
            //any error in a step if handled here
            if(err){

                $msg.text(err);
                $progressBar.addClass('error');
                return;
            }

            //or if everything runs well
            $msg.text(__('Done'));
            $progressBar.addClass('success');

            //delay redirect for better UX
            _.delay(function(){
                window.location = successUrl;
            }, 600);
        });
    };

    /**
     * The index controller
     * @exports taoUpdate/controller/update/index
     */
    var indexController = {

        /**
         * Controller's entry point.
         */
        start : function start(){
            var $container = $('#tao-update-container');

            //get available updates
            loadUpdateData().done(function(data){
                var $upgraders;

                buildUpdateList($container, data);

                $upgraders = $('.upgrader', $container);

                $upgraders.off('click').on('click', function(e){
                    var $elt, $upgrading, $progressBar, $msg;

                    e.preventDefault();
                    $upgraders.prop('disabled', true);

                    $elt = $(this);
                    $upgrading = $elt.parents('.update-version').find('.upgrading');

                    launchUpgrade($elt.data('version'), $container.data('success'), $upgrading);
                });
            });
        }
    };

    return indexController;
});
