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

class AdminSendCloudController extends ModuleAdminController {

    public function __construct()
    {
        $this->table = 'sendcloud';
        $this->className = 'Send_Cloud';
        $this->lang = false;
        $this->bootstrap = true;

        $this->deleted = false;
        $this->allow_export = false;
        $this->list_id = 'sendcloud';
        $this->identifier = 'id_sendcloud';
        $this->_defaultOrderBy = 'id_sendcloud';
        $this->_defaultOrderWay = 'DESC';
        $this->context = Context::getContext();

        /* $this->addRowAction('edit'); */
        $this->addRowAction('delete'); 
        
        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?', [], 'Modules.Myselphone.AdminSendCloudController.php')
            )
        );
        
        $this->_where .= " AND a.update = 1";

        $this->fields_list = array(
            'id_sendcloud'=>array(
                'title' => $this->l('ID', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'align'=>'center',
                'class'=>'fixed-width-xs'
            ),
            'order_number'=>array(
                'title'=>$this->l('Order Reference', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'width'=>'auto'
            ),
            'id_parcel'=>array(
                'title'=>$this->l('Parcel', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'width'=>'auto'
            ),
            'tracking_number'=>array(
                'title'=>$this->l('Tracking Number', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'width'=>'auto'
            ),
            'tracking_url'=>array(
                'title'=>$this->l('Tracking Url', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'width'=>'auto',
                'callback' => 'displayUrl'
            ),
            'label_printer'=>array(
                'title'=>$this->l('Label Url', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'width'=>'auto',
                'callback' => 'displayUrl'
            ),
            'update' => array(
                'title' => $this->l('Mise à jour', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'active' =>'status',
                'type' =>'bool',
                'align' =>'center',
                'class' =>'fixed-width-xs',
                'orderby' => false,
            )
        );
    }

    public function displayUrl($value, $row) {
        return "<a href='$value' target='_blank' onclick='event.stopPropagation()'>$value<a/>";
    }

    public function renderForm()
    {
        if (!($sendcloud = $this->loadObject(true))) {
            return;
        }

        
        /* $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('sendcloud', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                'icon' => 'icon-certificate'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Nom de la couleur', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                    'name' => 'name',
                    'col' => 4,
                    'lang' => true,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Valeur', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                    'name' => 'value',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                    'name' => 'avatar',
                    'image' => $image_url ? $image_url : false,
                    'size' => $image_size,
                    'display_image' => true,
                    'col' => 6,
                    'hint' => $this->l('Upload a sendcloud logo from your computer.', [], 'Modules.Myselphone.AdminSendCloudController.php')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable', [], 'Modules.Myselphone.AdminSendCloudController.php'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled', [], 'Modules.Myselphone.AdminSendCloudController.php')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled', [], 'Modules.Myselphone.AdminSendCloudController.php')
                        )
                    )
                )
            )
        );

        if (!($sendcloud = $this->loadObject(true))) {
            return;
        }


        $this->fields_form['submit'] = array(
            'title' => $this->l('Save', [], 'Modules.Myselphone.AdminSendCloudController.php')
        );

        foreach ($this->_languages as $language) {
            $this->fields_value['name_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $sendcloud,
                'name',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        } */

        return parent::renderForm();
    }

    

    public function l($string, $params = [], $domaine = 'Modules.Myselphone.AdminSendCloudController.php', $local = null){
        if(_PS_VERSION_ >= '1.7'){
            if($params === null || !is_array($params)){
                $params = [];
            }
            return $this->module->getTranslator()->trans($string, $params, $domaine, $local);
        }else{
            return parent::l($string, null, false, true);
        }
    }
}
