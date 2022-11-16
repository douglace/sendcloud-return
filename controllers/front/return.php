<?php

class SendcloudReturnModuleFrontController extends ModuleFrontController
{

    /**
     * Undocumented variable
     *
     * @var Order
     */
    public $order;

    public function init()
    {
        if(!$this->context->customer->isLogged()){
            $this->redirect_after = $this->context->link->getPageLink('authentication', true);
            $this->redirect();
        }
        
        if (Tools::getValue("id_order", false) && Validate::isLoadedObject(new Order(Tools::getValue("id_order", false)))) {
            $this->order = new Order(Tools::getValue("id_order"), $this->context->language->id);
            if (!SendcloudOrder::getNumberOfDays($this->order->id, $this->order->id_shop) || !$this->order->isPaidAndShipped() || $this->order->id_customer != $this->context->customer->id) {
                $this->errors[] = $this->trans("Error to load Order");
                return parent::init();
            }
        } else {
            $this->errors[] = $this->trans("Error to load Order");
            return parent::init();
        }

        //Check if parcel exist
        $id_parcel = SendCloudRequest::checkParcel($this->order);
        if ($id_parcel) {
            //Update parcel
            $parcel = SendCloudRequest::createParcel($this->order, $id_parcel);

            if (empty($parcel) || is_null($parcel)) {
                $this->errors[] = $this->trans("SendCloud Error.");
            } else {
                $sendcloud = Send_Cloud::getSendcloudWithParcel($id_parcel);
                if($sendcloud == null || !Validate::isLoadedObject($sendcloud)){
                    $sendcloud = new Send_Cloud();
                    $sendcloud->id_order = $this->order->id;
                    $sendcloud->order_number = $this->order->reference;
                    $sendcloud->id_parcel = (int) $parcel["parcel"]["id"];
                    $sendcloud->save();
                }else{
                    $sendcloud->id_order = $this->order->id;
                    $sendcloud->order_number = $this->order->reference;
                    $sendcloud->id_parcel = (int) $parcel["parcel"]["id"];
                    $sendcloud->save();
                }
                $this->redirect_after = SendCloudRequest::getUrlReturnsPortail();
                $this->redirect();
            }
        } else {
            //Create parcel
            $parcel = SendCloudRequest::createParcel($this->order);
            
            if (empty($parcel) || is_null($parcel)) {
                $this->errors[] = $this->trans("SendCloud Error.");
            } else {
                $sendcloud = new Send_Cloud();
                $sendcloud->id_order = $this->order->id;
                $sendcloud->order_number = $this->order->reference;
                $sendcloud->id_parcel = (int) $parcel["parcel"]["id"];
                $sendcloud->save();

                $this->redirect_after = SendCloudRequest::getUrlReturnsPortail();
                $this->redirect();
            }
        }

        return parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            "order" => $this->order
        ]);

        $this->setTemplate("return.tpl");
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
