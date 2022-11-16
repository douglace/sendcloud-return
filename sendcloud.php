<?php

/**
 * 2007-2022 PrestaShop
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
 *  @copyright 2007-2022 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Sendcloud extends CarrierModule
{
    protected $config_form = false;
    protected $tabs = [];

    public function __construct()
    {
        require_once dirname(__FILE__) . "/classes/Module.Classes.php";

        $this->name = 'sendcloud';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Anthony';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        $this->tabs = [
            [
                "name" => $this->l("Retours SendCloud"),
                "class_name" => "AdminSendCloud",
                "parent" => "AdminParentShipping"
            ]
        ];

        parent::__construct();

        $this->displayName = $this->l('Sendcloud');
        $this->description = $this->l('Retours client');
        
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        /* $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRanges($carrier); */
        Configuration::updateValue('SENDCLOUD_LIVE_MODE', true);
        Configuration::updateValue('SENDCLOUD_ORDER_RETURN_NB_DAYS', 14);

        require dirname(__FILE__) . "/sql/install.php";

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('updateCarrier') &&
            $this->installTabs(true);
    }

    public function uninstall()
    {
        Configuration::deleteByName('SENDCLOUD_LIVE_MODE');

        require dirname(__FILE__) . "/sql/uninstall.php";

        return parent::uninstall() &&
            $this->installTabs(false);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSendcloudModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
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
        $helper->submit_action = 'submitSendcloudModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
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
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'SENDCLOUD_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
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
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid public key'),
                        'name' => 'SENDCLOUD_PUBLIC_KEY',
                        'label' => $this->l('Public Key'),
                        'required' => true,
                    ),
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid private key'),
                        'name' => 'SENDCLOUD_PRIVATE_KEY',
                        'label' => $this->l('Private Key'),
                        'required' => true,
                    ),
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Nom pour votre portail de retour'),
                        'name' => 'SENDCLOUD_BRAND_DOMAINE',
                        'label' => $this->l('Portail De Retour'),
                        'required' => true,
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'SENDCLOUD_ORDER_RETURN_NB_DAYS',
                        'label' => $this->l('Nombre de jours'),
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l("Entrez un nombre de jours valide"),
                        'required' => true,
                    ),
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'name' => 'SENDCLOUD_RETURN_PORTAIL_URL',
                        'label' => $this->l('Url Portail'),
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l("Url du Portail de Retour"),
                        'required' => true,
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
            'SENDCLOUD_LIVE_MODE' => Configuration::get('SENDCLOUD_LIVE_MODE', true),
            'SENDCLOUD_PUBLIC_KEY' => Configuration::get('SENDCLOUD_PUBLIC_KEY', null),
            'SENDCLOUD_PRIVATE_KEY' => Configuration::get('SENDCLOUD_PRIVATE_KEY', null),
            'SENDCLOUD_ORDER_RETURN_NB_DAYS' => Configuration::get('SENDCLOUD_ORDER_RETURN_NB_DAYS', null),
            'SENDCLOUD_BRAND_DOMAINE' => Configuration::get('SENDCLOUD_BRAND_DOMAINE', null),
            'SENDCLOUD_RETURN_PORTAIL_URL' => Configuration::get('SENDCLOUD_RETURN_PORTAIL_URL', null),
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

    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (Context::getContext()->customer->logged == true) {
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address = new Address($id_address_delivery);

            /**
             * Send the details through the API
             * Return the price sent by the API
             */
            return 10;
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('My super carrier');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang)
            $carrier->delay[$lang['id_lang']] = $this->l('Super fast delivery');

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            Configuration::updateValue('MYSHIPPINGMODULE_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group)
            $groups_ids[] = $group['id_group'];

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone)
            $carrier->addZone($zone['id_zone']);
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
         */
    }

    public function hookDisplayCustomerAccount()
    {
        $url_sendcloud_orders = $this->context->link->getModuleLink($this->name, 'orders');
        $this->context->smarty->assign("url_sendcloud_orders", $url_sendcloud_orders);
        return $this->context->smarty->fetch($this->local_path . "views/templates/hook/customer_account.tpl");
    }

    public function hookDisplayHeader()
    {
        $this->UpdateSendClouds();
        $this->DeleteSendClouds();
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->UpdateSendClouds();
        $this->DeleteSendClouds();
    }

    public function installTabs($install = true): bool
    {
        if ($install === true) {
            $languages = Language::getLanguages();
            foreach ($this->tabs as $t) {
                $id_tab = Tab::getIdFromClassName($t["class_name"]);
                if (!$id_tab) {
                    $tab = new Tab();
                    $tab->class_name = $t["class_name"];
                    $tab->module = $this->name;
                    $tab->id_parent = Tab::getIdFromClassName($t["parent"]);
                    foreach ($languages as $lang) {
                        $tab->name[$lang["id_lang"]] = $t["name"];
                    }
                    $tab->save();
                }
            }
            return true;
        } else {
            foreach ($this->tabs as $t) {
                $id = Tab::getIdFromClassName($t["class_name"]);
                if ($id) {
                    $tab = new Tab($id);
                    $tab->delete();
                }
                return true;
            }
        }
    }

    public function UpdateSendClouds()
    {
        try {
            $sendclouds = Send_Cloud::getSendclouds(0);
            foreach ($sendclouds as $sendcloud) {
                $response = SendCloudRequest::getParcels($sendcloud->order_number, SendcloudOrder::$ready_to_send);
                if ($response && !empty($response) && !empty($response["parcels"])) {
                    $parcel = $response["parcels"][0];

                    $sd = Send_Cloud::getSendcloudWithParcel((int) $parcel["id"]);
                    if ($sd || Validate::isLoadedObject($sd)) {
                        continue;
                    }

                    $sendcloud->id_parcel = (int) $parcel["id"];
                    $sendcloud->tracking_number = $parcel["tracking_number"];
                    $sendcloud->tracking_url = $parcel["tracking_url"];
                    $sendcloud->label_printer = $parcel["documents"][0]["link"];
                    $sendcloud->update = true;
                    $sendcloud->save();

                    $order = new Order($sendcloud->id_order);
                    $order_return = new OrderReturn(null, $this->context->language->id, $this->context->shop->id);
                    $order_return->id_customer = $order->id_customer;
                    $order_return->id_order = $order->id;
                    $order_return->question = "Default";
                    $order_return->state = SendcloudOrder::$return_waitting_for_package;
                    $order_return->save();

                    $order_detail_list = [];
                    $product_qty_list = [];
                    foreach ($parcel["parcel_items"] as $item) {
                        $ids = explode('@', $item["sku"]);
                        $id_product = (int)$item["product_id"];
                        $id_product_attribute = (int)$ids[1];
                        $order_details = SendcloudOrder::getOrdersDetails($order->id, null, false, $id_product, $id_product_attribute);
                        if ($order_details && !empty($order_details) && !is_null($order_details)) {
                            $order_detail = $order_details[0];
                            $order_detail_list[] = (int) $order_detail->id;
                            $product_qty_list[] = (int) $item["quantity"];

                            Db::getInstance()->insert("order_return_sendcloud", [
                                "id_sendcloud" => $sendcloud->id,
                                "id_order_return" => $order_return->id,
                                "id_order_detail" => (int) $order_detail->id,
                                "quantity" =>  (int) $item["quantity"],
                            ]);
                        }
                    }

                    $order_return->addReturnDetail($order_detail_list, $product_qty_list, [], []);
                }
            }
        } catch (Exception $e) {
            /* var_dump($e->getMessage());
            die; */
        }
    }

    public function DeleteSendClouds()
    {
        try {
            $sendclouds = Send_Cloud::getSendclouds(0);
            foreach ($sendclouds as $sendcloud) {
                $date = new DateTime($sendcloud->date_upd);
                $diff = $date->diff(new DateTime());
                if ($diff->days && $diff->days >= 1) {
                    SendCloudRequest::deleteParcel($sendcloud->id_parcel);
                    $sendcloud->delete();
                }
            }
        } catch (Exception $e) {

        }
    }
}
