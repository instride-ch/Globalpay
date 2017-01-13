<?php

namespace Globalpay;

use Globalpay\Plugin\Install;
use Pimcore\API\Plugin as PluginLib;
use Pimcore\Model\Object\ClassDefinition;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    /**
     * @var \Zend_Translate
     */
    protected static $_translate;

    /**
     *
     */
    public function init()
    {
        parent::init();

        require_once PIMCORE_PLUGINS_PATH.'/Globalpay/config/helper.php';

        \Zend_Controller_Action_HelperBroker::addPath(PIMCORE_PLUGINS_PATH.'/Globalpay/lib/Globalpay/Controller/Action/Helper', 'Globalpay\Controller\Action\Helper');

        \Pimcore::getEventManager()->attach('system.startup', function (\Zend_EventManager_Event $e) {
            $frontController = $e->getTarget();

            if ($frontController instanceof \Zend_Controller_Front) {
                $frontController->registerPlugin(new Controller\Plugin\GatewayRouter());
            }
        });
    }

    /**
     * @return bool
     */
    public static function install()
    {
        $install = new Install();

        return $install->fullInstall();
    }

    /**
     * @return bool
     */
    public static function uninstall()
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isInstalled()
    {
        return ClassDefinition::getByName('GlobalpayPayment') instanceof ClassDefinition;
    }

    /**
     * get translation directory
     *
     * @return string
     */
    public static function getTranslationFileDirectory()
    {
        return PIMCORE_PLUGINS_PATH . '/Globalpay/static/texts';
    }

    /**
     * get translation file
     *
     * @param string $language
     * @return string path to the translation file relative to plugin directory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(self::getTranslationFileDirectory() . "/$language.csv")) {
            return "/Globalpay/static/texts/$language.csv";
        } else {
            return '/Globalpay/static/texts/en.csv';
        }
    }

    /**
     * get translate
     *
     * @param $lang
     * @return \Zend_Translate
     */
    public static function getTranslate($lang = null)
    {
        if (self::$_translate instanceof \Zend_Translate) {
            return self::$_translate;
        }

        if (is_null($lang)) {
            try {
                $lang = \Zend_Registry::get('Zend_Locale')->getLanguage();
            } catch (\Exception $e) {
                $lang = 'en';
            }
        }

        self::$_translate = new \Zend_Translate(
            'csv',
            PIMCORE_PLUGINS_PATH . self::getTranslationFile($lang),
            $lang,
            ['delimiter' => ',']
        );

        return self::$_translate;
    }
}
