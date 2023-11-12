<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class TestLang extends Module
{
    public function __construct()
    {
        $this->name = 'testlang';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Doryan Fourrichon';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];

        // on récupère le fonction du constructeur de la méthode __construct de module
        parent::__construct();
        $this->bootstrap = true;
        $this->displayName = $this->l('Testlang');
        $this->description = $this->l('Module pour tester les langs');
        $this->confirmUninstall = $this->l('Do you want to delete this module');
    }

    public function install()
    {
        

        if(!parent::install() ||
        !Configuration::updateValue('NAME_LANG','') ||
        !Configuration::updateValue('FIRST_NAME_LANG','') ||
        !Configuration::updateValue('DESC_LANG','') ||
        !Configuration::updateValue('RESUME_LANG','') ||
        !Configuration::updateValue('PAYS_LANG','')
        )
        {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if(!parent::uninstall() ||
        !Configuration::deleteByName('NAME_LANG') ||
        !Configuration::deleteByName('FIRST_NAME_LANG') ||
        !Configuration::deleteByName('DESC_LANG') ||
        !Configuration::deleteByName('RESUME_LANG') ||
        !Configuration::deleteByName('PAYS_LANG')
        )
        {
            return false;
        }

        foreach(Language::getLanguages(false) as $lang)
        {
            Configuration::deleteByName('NAME_LANG_'.$lang['id_lang']);
            Configuration::deleteByName('FIRST_NAME_LANG_'.$lang['id_lang']);
            Configuration::deleteByName('DESC_LANG_'.$lang['id_lang']);
            Configuration::deleteByName('RESUME_LANG_'.$lang['id_lang']);
            Configuration::deleteByName('PAYS_LANG_'.$lang['id_lang']);
        }


            return true;
        
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $field_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings Language'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'name' => 'NAME_LANG',
                    'label' => $this->l('Name'),
                    'required' => true,
                    'lang' => true
                ],
                [
                    'type' => 'text',
                    'name' => 'FIRST_NAME_LANG',
                    'label' => $this->l('Firstname'),
                    'required' => true,
                    'lang' => true
                ],
                [
                    'type' => 'textarea',
                    'name' => 'DESC_LANG',
                    'label' => $this->l('Description'),
                    'required' => true,
                    'lang' => true
                ],
                [
                    'type' => 'text',
                    'name' => 'RESUME_LANG',
                    'label' => $this->l('Resume'),
                    'required' => true,
                    'lang' => true
                ],
                [
                    'type' => 'text',
                    'name' => 'PAYS_LANG',
                    'label' => $this->l('Pays'),
                    'required' => true,
                    'lang' => true
                ]
            ],
            'submit' => [
                'name' => 'send',
                'class' => 'btn btn-primary',
                'title' => $this->l('Validate')
            ]

        ];

        $helper = new HelperForm();
        $helper->module  = $this;
        $helper->name_controller = $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $default_lang = Configuration::get('PS_LANG_DEFAULT');

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = $default_lang;

        foreach (Language::getLanguages(false) as $lang) {
            // dump($lang);
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'locale' => $lang['locale'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            ];

            $name = 'NAME_LANG_'.$lang['id_lang'];
            $firstname = 'FIRST_NAME_LANG_'.$lang['id_lang'];
            $descr = 'DESC_LANG_'.$lang['id_lang'];
            $resume = 'RESUME_LANG_'.$lang['id_lang'];
            $pays = 'PAYS_LANG_'.$lang['id_lang'];

            $helper->fields_value['NAME_LANG'][$lang['id_lang']] = Configuration::get($name);
            $helper->fields_value['FIRST_NAME_LANG'][$lang['id_lang']] = Configuration::get($firstname);
            $helper->fields_value['DESC_LANG'][$lang['id_lang']] = Configuration::get($descr);
            $helper->fields_value['RESUME_LANG'][$lang['id_lang']] = Configuration::get($resume);
            $helper->fields_value['PAYS_LANG'][$lang['id_lang']] = Configuration::get($pays);
        }

        return $helper->generateForm($field_form);
    }

    public function postProcess()
    {
        if(Tools::isSubmit('send'))
        {
            foreach (Language::getLanguages(false) as $lang) {
                Configuration::updateValue('NAME_LANG_'.$lang['id_lang'], Tools::getValue('NAME_LANG_'.$lang['id_lang']),true);
                Configuration::updateValue('FIRST_NAME_LANG_'.$lang['id_lang'], Tools::getValue('FIRST_NAME_LANG_'.$lang['id_lang']),true);
                Configuration::updateValue('DESC_LANG_'.$lang['id_lang'], Tools::getValue('DESC_LANG_'.$lang['id_lang']),true);
                Configuration::updateValue('RESUME_LANG_'.$lang['id_lang'], Tools::getValue('RESUME_LANG_'.$lang['id_lang']),true);
                Configuration::updateValue('PAYS_LANG_'.$lang['id_lang'], Tools::getValue('PAYS_LANG_'.$lang['id_lang']),true);
            }
        }
    }

}