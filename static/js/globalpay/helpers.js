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

pimcore.registerNS('globalpay.helpers.x');

globalpay.helpers.showAbout = function () {
    var html = '<div class="pimcore_about_window">';
    html += '<br /><img src="/plugins/Globalpay/static/img/globalpay_logo.svg" style="width: 300px;"><br />';
    //html += '<br /><strong>Version: ' + globalpay.settings.version + '</strong>';
    //html += '<br /><strong>Build: ' + globalpay.settings.build + '</strong>';
    html += '<br /><br />&copy; by w-vision, Sursee, Switzerland (<a href="http://www.w-vision.ch/" target="_blank">w-vision.ch</a>)';
    html += '<br />a proud member of the <a href="http://woche-pass.ch/" target="_blank">Woche-Pass AG</a>';
    html += '<br><br><a href="http://www.w-vision.ch/de/startseite#kennen-lernen" target="_blank">Contact</a> | ';
    html += '<a href="https://github.com/w-vision/Globalpay/blob/master/LICENSE.md" target="_blank">License</a> | ';
    html += '<a href="http://www.w-vision.ch/de/startseite#das-unternehmen" target="_blank">Team</a>';
    html += '</div>';

    var win = new Ext.Window({
        title: t('globalpay_about'),
        width: 500,
        height: 350,
        bodyStyle: 'padding: 10px;',
        modal: true,
        html: html
    });

    win.show();
};
