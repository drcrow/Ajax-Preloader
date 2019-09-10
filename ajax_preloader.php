<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ajax_preloader extends Module
{
    protected $config_form = false;
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'ajax_preloader';
        $this->tab = 'front_office_features';
        $this->version = '1.5.0';
        $this->author = 'Agustín Fiori';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ajax Preloader');
        $this->description = $this->l('Adds a custom overlay with loading animation for the ajax calls');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ajax_preloader/views/templates/hook/';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('displayFooterBefore') &&
            $this->registerHook('displayFooterAfter');
    }

    public function uninstall()
    {

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAjax_preloaderModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAjax_preloaderModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => 'Hook',
                        'name' => 'AJAX_PRELOADER_HOOK',
                        'desc' => $this->l('The hook where the code will be injected'),
                        'class' => 'rte',
                        'autoload_rte' => true,
                        'options' => array(
                            'query' => $options = array(
                                    array('id_option' => 0, 'name' => '--none--'),
                                    array('id_option' => 1, 'name' => 'displayFooter'),
                                    array('id_option' => 2, 'name' => 'displayFooterBefore'),
                                    array('id_option' => 3, 'name' => 'displayFooterAfter'),
                            ),
                            'id' => 'id_option',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Spinner Color'),
                        'name' => 'AJAX_PRELOADER_COLOR'
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Background Color'),
                        'name' => 'AJAX_PRELOADER_BG_COLOR'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Debug'),
                        'name' => 'AJAX_PRELOADER_DEBUG',
                        'is_bool' => true,
                        'desc' => $this->l('The browser console will show "Ajax Preloader Show" and "Ajax Preloader Hide"'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'AJAX_PRELOADER_HOOK' => Configuration::get('AJAX_PRELOADER_HOOK', null, null, null, 1),
            'AJAX_PRELOADER_COLOR' => Configuration::get('AJAX_PRELOADER_COLOR', null, null, null, '#FF0000'),
            'AJAX_PRELOADER_BG_COLOR' => Configuration::get('AJAX_PRELOADER_BG_COLOR', null, null, null, '#00FF00'),
            'AJAX_PRELOADER_DEBUG' => Configuration::get('AJAX_PRELOADER_DEBUG', null, null, null, false),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayFooter()
    {
        if(Configuration::get('AJAX_PRELOADER_HOOK') == 1){
            $this->smarty->assign(array(
                'preloader_debug' =>    Configuration::get('AJAX_PRELOADER_DEBUG', null, null, null, false),
                'preloader_bg_color' => Configuration::get('AJAX_PRELOADER_BG_COLOR', null, null, null, '#00FF00'),
                'preloader_color' =>    Configuration::get('AJAX_PRELOADER_COLOR', null, null, null, '#FF0000')
            ));
            return $this->display(__FILE__, 'ajax_preloader_footer.tpl', $this->getCacheId($this->templateFile.'ajax_preloader_footer'));
        }
    }

    public function hookDisplayFooterBefore()
    {
        if(Configuration::get('AJAX_PRELOADER_HOOK') == 2){
            $this->smarty->assign(array(
                'preloader_debug' =>    Configuration::get('AJAX_PRELOADER_DEBUG', null, null, null, false),
                'preloader_bg_color' => Configuration::get('AJAX_PRELOADER_BG_COLOR', null, null, null, '#00FF00'),
                'preloader_color' =>    Configuration::get('AJAX_PRELOADER_COLOR', null, null, null, '#FF0000')
            ));
            return $this->display(__FILE__, 'ajax_preloader_footer.tpl', $this->getCacheId($this->templateFile.'ajax_preloader_footer'));
        }
    }

    public function hookDisplayFooterAfter()
    {
        if(Configuration::get('AJAX_PRELOADER_HOOK') == 3){
            $this->smarty->assign(array(
                'preloader_debug' =>    Configuration::get('AJAX_PRELOADER_DEBUG', null, null, null, false),
                'preloader_bg_color' => Configuration::get('AJAX_PRELOADER_BG_COLOR', null, null, null, '#00FF00'),
                'preloader_color' =>    Configuration::get('AJAX_PRELOADER_COLOR', null, null, null, '#FF0000')
            ));
            return $this->display(__FILE__, 'ajax_preloader_footer.tpl', $this->getCacheId($this->templateFile.'ajax_preloader_footer'));
        }
    }
}
