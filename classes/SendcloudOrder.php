<?php

abstract class SendcloudOrder
{

    public static $cancelled = 2000;
    public static $ready_to_send = 1000;
    public static $no_label = 999;

    public static $return_waitting_for_package = 2;
    public static $return_package_received = 1;

    public static function getNumberOfDays(int $id_order, int $id_shop)
    {
        $nb_return_days = (int) Configuration::get('SENDCLOUD_ORDER_RETURN_NB_DAYS');
        if (!$nb_return_days) {
            return true;
        }
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT TO_DAYS("' . date('Y-m-d') . ' 00:00:00") - TO_DAYS(`delivery_date`)  AS days FROM `' . _DB_PREFIX_ . 'orders`
        WHERE `id_order` = ' . $id_order);
        if ($result['days'] <= $nb_return_days) {
            return true;
        }

        return false;
    }

    public static function getOrders(int $id_shop, int $id_customer, int $id_lang = null)
    {
        $id_lang = $id_lang ?? Context::getContext()->language->id;

        $nb_return_days = (int) Configuration::get('SENDCLOUD_ORDER_RETURN_NB_DAYS');
        $q = new DbQuery();
        $q->select('o.`id_order`')
            ->from("orders", "o")
            ->where('(TO_DAYS("' . date('Y-m-d') . ' 00:00:00") - TO_DAYS(o.`delivery_date`)) <= ' . $nb_return_days)
            ->where("o.`id_shop` = $id_shop")
            ->where("o.`id_customer` = $id_customer")
            ->orderBy("o.`delivery_date` DESC");

        $orders = Db::getInstance()->executeS($q);

        $orders = array_map(function ($order) use ($id_lang) {
            return new Order($order["id_order"], $id_lang);
        }, $orders);

        return array_filter($orders, function ($order) {
            return $order->isPaidAndShipped();
        });
    }

    public static function getOrderWithReference(string $reference, int $id_lang = null)
    {
        $id_lang = $id_lang ?? Context::getContext()->language->id;
        $q = new DbQuery();
        $q->select('o.`id_order`')
            ->from("orders", "o")
            ->where("o.reference = '$reference'");

        $order = Db::getInstance()->getRow($q);
        if ($order && !empty($order) || !is_null($order)) {
            return new Order((int) $order["id_order"], $id_lang);
        }
        return false;
    }


    public static function getOrdersDetails(int $id_order, int $id_lang = null, bool $return = false, int $id_product = null, int $id_product_attribute = null)
    {
        $id_lang = $id_lang ?? Context::getContext()->language->id;

        $q = new DbQuery();
        $q->select('od.`id_order_detail`')
            ->from("order_detail", "od")
            ->where("od.`id_order` = $id_order")
            ->orderBy("od.`id_order_detail` ASC");

        if ($id_product) {
            $q->where("od.product_id = $id_product");
        }

        if ($id_product_attribute) {
            $q->where("od.product_attribute_id = $id_product_attribute");
        }

        $orders_detatils = Db::getInstance()->executeS($q);

        $orders_detatils = array_map(function ($order_detatil) use ($id_lang) {
            return new OrderDetail($order_detatil["id_order_detail"], $id_lang);
        }, $orders_detatils);

        if (!$return) {
            $orders_detatils = array_filter($orders_detatils, function ($order_detatil) {
                $res = Db::getInstance()->executeS('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'order_return_detail`
            WHERE `id_order_detail` = ' . (int) $order_detatil->id);
                return !($res && !empty($res) && !is_null($res));
            });
        }

        return $orders_detatils;
    }

    public static function getOrderQuantity(int $id_order)
    {
        $q = new DbQuery();
        $q->select('COUNT(od.`product_quantity`)')
            ->from("order_detail", "od")
            ->where("od.`id_order` = $id_order");

        return (int) Db::getInstance()->getValue($q);
    }

    public static function getOrderWeight(int $id_order)
    {
        $q = new DbQuery();
        $q->select('COUNT(od.`product_weight`)')
            ->from("order_detail", "od")
            ->where("od.`id_order` = $id_order");

        return round((float) Db::getInstance()->getValue($q), 2);
    }
}
