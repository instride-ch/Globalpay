/**
 * w-vision
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016 Woche-Pass AG (http://www.w-vision.ch)
 * @license    GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS('pimcore.plugin.globalpay');
pimcore.plugin.globalpay = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return 'pimcore.plugin.globalpay';
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function(params,broker) {
        var toolbar = pimcore.globalmanager.get('layout_toolbar');

        this._menu = new Ext.menu.Menu({
            items: [
                {
                    text: t('globalpay_settings'),
                    iconCls: 'globalpay_icon_settings',
                    handler: function() {
                        try {
                            pimcore.globalmanager.get('globalpay_settings').activate();
                        }
                        catch (e) {
                            pimcore.globalmanager.add('globalpay_settings', new pimcore.plugin.globalpay.settings());
                        }
                    }
                },
                // {
                //     text: t('globalpay_update'),
                //     iconCls: 'globalpay_icon_update',
                //     handler: function () {
                //         try {
                //             pimcore.globalmanager.get('globalpay_update').activate();
                //         }
                //         catch (e) {
                //             pimcore.globalmanager.add('globalpay_update', new pimcore.tool.genericiframewindow('globalpay_update', '/plugin/Globalpay/admin_console/update?no-cache=true', 'globalpay_icon_update', 'Update'));
                //         }
                //     }
                // },
                {
                    text: t('globalpay_about'),
                    iconCls: 'globalpay_icon_about',
                    handler: function () {
                        globalpay.helpers.showAbout();
                    }
                }
            ],
            shadow: false,
            cls: 'pimcore_navigation_flyout'
        });

        Ext.get('pimcore_navigation').down('ul').insertHtml('beforeEnd', '<li id="pimcore_menu_globalpay" data-menu-tooltip="Globalpay" class="pimcore_menu_item pimcore_menu_needs_children"></li>');
        Ext.get('pimcore_menu_globalpay').on('mousedown', function(e, el) {
            toolbar.showSubMenu.call(this._menu, e, el);
        }.bind(this));
    }
});

var globalpayPlugin = new pimcore.plugin.globalpay();
