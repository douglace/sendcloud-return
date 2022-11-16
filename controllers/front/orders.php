<?php

class SendcloudOrdersModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        if (!$this->context->customer->isLogged()) {
            $this->redirect_after = $this->context->link->getPageLink('authentication', true);
            $this->redirect();
        }
        return parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            "orders" => SendcloudOrder::getOrders($this->context->shop->id, $this->context->customer->id, $this->context->language->id),
            "context_link" => $this->context->link,
            "module_name" => $this->module->name,
            "SENDCLOUD_ORDER_RETURN_NB_DAYS" => Configuration::get('SENDCLOUD_ORDER_RETURN_NB_DAYS')
        ]);

        $this->setTemplate("orders.tpl");
    }

    public function setTemplate($template, $params = [], $locale = null)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            parent::setTemplate($template, $params, $locale);
        } else {
            parent::setTemplate("module:sendcloud/views/templates/front/" . $template, $params, $locale);
        }
    }
}
