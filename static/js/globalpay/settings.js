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

pimcore.registerNS('pimcore.plugin.globalpay.settings');
pimcore.plugin.globalpay.settings = Class.create({

    providerPanels: {},

    initialize: function() {
        this.getData();
    },

    getData: function() {
        Ext.Ajax.request({
            url: '/plugin/Globalpay/admin/get',
            success: function(response) {
                this.data = Ext.decode(response.responseText);

                this.getPanel();

                Ext.Ajax.request({
                    url: '/plugin/Globalpay/admin/get-active-providers',
                    success: function(response) {
                        var result = Ext.decode(response.responseText);

                        Ext.each(result, function(provider) {
                            this.addProviderPanel(provider.name, provider);
                        }.bind(this));

                    }.bind(this)
                });

            }.bind(this)
        });
    },

    getValue: function(name, key) {
        var current = null;

        if (this.data.values.hasOwnProperty(name)) {
            current = this.data.values[name];

            if (current.hasOwnProperty(key)) {
                current = current[key];
            }
        }

        if (typeof current != 'object' && Object.prototype.toString.call(current) !== '[object Array]' && typeof current != 'function') {
            return current;
        }

        return '';
    },

    getPanel: function() {
        if (!this.panel) {
            this.providerStore = new Ext.data.Store({
                fields: ['name'],
                proxy: {
                    type: 'ajax',
                    url: '/plugin/Globalpay/admin/get-providers',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                }
            });
            this.providerStore.load();

            this.panel = Ext.create('Ext.tab.Panel', {
                id: 'globalpay_settings',
                title: t('globalpay_settings'),
                iconCls: 'globalpay_icon_settings',
                bodyPadding: 20,
                border: false,
                layout: 'fit',
                closable: true,
                buttons: [
                    {
                        text: t('save'),
                        handler: this.save.bind(this),
                        iconCls: 'pimcore_icon_apply'
                    }
                ],
                items: [
                    {
                        xtype: 'panel',
                        title: t('globalpay_settings_providers'),
                        iconCls: 'globalpay_icon_providers',
                        border: false,
                        items: [
                            {
                                xtype: 'combo',
                                fieldLabel: t('globalpay_settings_provider'),
                                store: this.providerStore,
                                displayField: 'name',
                                valueField: 'name',
                                labelWidth: 150,
                                width: 500,
                                forceSelection: true,
                                triggerAction: 'all',
                                name: 'providerToAdd'
                            },
                            {
                                xtype: 'button',
                                text: t('globalpay_settings_add_provider'),
                                style: 'margin-left: 155px;',
                                handler: function(btn) {
                                    var provider = btn.up('panel').down('combo').getValue();

                                    Ext.Ajax.request({
                                        url: '/plugin/Globalpay/admin/add-provider',
                                        params: {provider: provider},
                                        success: function(response) {
                                            var data = Ext.decode(response.responseText);

                                            if (data.success) {
                                                this.addProviderPanel(provider, data);

                                                this.providerStore.load();
                                            } else {
                                                pimcore.helpers.showNotification(t('error'), '', 'error');
                                            }

                                        }.bind(this)
                                    });
                                }.bind(this),
                                iconCls: 'pimcore_icon_apply'
                            }
                        ]
                    }
                ]
            });

            var tabPanel = Ext.getCmp('pimcore_panel_tabs');
            tabPanel.add(this.panel);
            tabPanel.setActiveItem('globalpay_settings');

            this.panel.on('destroy', function() {
                pimcore.globalmanager.remove('globalpay_settings');
            }.bind(this));
        }

        return this.panel;
    },

    addProviderPanel: function(name, data) {
        var me = this;
        var items = [];

        for (var key in data.settings) {
            if (data.settings.hasOwnProperty(key)) {
                var providerName = ('GLOBALPAY.' + name).toUpperCase();

                items.push({
                    fieldLabel: key,
                    name: key,
                    value: this.getValue(providerName, key)
                });
            }
        }

        var panel = new Ext.form.Panel({
            title: name,
            iconCls: ('globalpay_icon_' + name).toLowerCase(),
            border: false,
            defaultType: 'textfield',
            defaults: {
                forceLayout: true,
                width: 500
            },
            closable: true,
            listeners: {
                beforeclose: function(panel, eOpts) {
                    Ext.MessageBox.confirm(t('globalpay_settings_remove'), t('globalpay_settings_remove_provider'), function(buttonValue) {
                        if (buttonValue === 'yes') {
                            Ext.Ajax.request({
                                url: '/plugin/Globalpay/admin/remove-provider',
                                params: {provider: name},
                                success: function(response) {
                                    var data = Ext.decode(response.responseText);

                                    if (data.success) {
                                        delete me.providerPanels[name];
                                        panel.destroy();
                                        pimcore.helpers.showNotification(t('success'), t('globalpay_settings_delete_success'), 'success');
                                    } else {
                                        pimcore.helpers.showNotification(t('error'), '', 'error');
                                    }

                                }.bind(this)
                            });
                        }
                    }.bind(this));

                    return false;
                }
            },
            fieldDefaults: {
                labelWidth: 150
            },
            items: items
        });

        this.providerPanels[name] = panel;

        this.panel.add(panel);

        return panel;
    },

    activate: function() {
        var tabPanel = Ext.getCmp('pimcore_panel_tabs');
        tabPanel.activate('globalpay_settings');
    },

    save: function() {
        var values = {};

        for (var key in this.providerPanels) {
            if (this.providerPanels.hasOwnProperty(key)) {
                var form = this.providerPanels[key];

                values['GLOBALPAY.' + key.toUpperCase()] = form.getForm().getFieldValues();
            }
        }

        Ext.Ajax.request({
            url: '/plugin/Globalpay/admin/set',
            method: 'post',
            params: {
                data: Ext.encode(values)
            },
            success: function(response) {
                try {
                    var res = Ext.decode(response.responseText);

                    if (res.success) {
                        pimcore.helpers.showNotification(t('success'), t('globalpay_settings_save_success'), 'success');
                    } else {
                        pimcore.helpers.showNotification(t('error'), t('globalpay_settings_save_error'), 'error', t(res.message));
                    }
                } catch(e) {
                    pimcore.helpers.showNotification(t('error'), t('globalpay_settings_save_error'), 'error');
                }
            }
        });
    }
});
