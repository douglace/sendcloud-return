<?php

use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\StatusColumn;

abstract class SendCloudRequest
{
    public static $url_parcels = "https://panel.sendcloud.sc/api/v2/parcels";
    public static $url_return_portal_settings = "https://panel.sendcloud.sc/api/v2/brand/{brand_domain}/return-portal?language={language}";
    public static $outgoing_parcel = "https://panel.sendcloud.sc/api/v2/brand/{brand_domain}/return-portal/outgoing";
    public static $detele_parcel = "https://panel.sendcloud.sc/api/v2/parcels/{id}/cancel";

    public static function sentRequest(string $url, bool $post = false, array $body = [])
    {
        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, $post);
            if ($post) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode(Configuration::get('SENDCLOUD_PUBLIC_KEY') . ':' . Configuration::get('SENDCLOUD_PRIVATE_KEY'))
            ));

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo curl_error($ch);
                die();
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code == intval(200)) {
                return json_decode($response, true);
            } else {
                SendCloudLog::writeToJsonLog($response);
                return [];
            }
        } catch (Exception $e) {
            throw $e;
        } finally {
            curl_close($ch);
        }
    }

    public static function returnPortalSettings()
    {
        $new_url = str_replace("{brand_domain}", self::getBrandDomaine(), self::$url_return_portal_settings);
        $new_url = str_replace("{language}", Context::getContext()->language->locale, $new_url);
        return self::sentRequest($new_url);
    }

    public static function outgoingParcel(string $identifier, string $postal_code, int $omit_service_points = 0)
    {
        $new_url = str_replace("{brand_domain}", self::getBrandDomaine(), self::$outgoing_parcel);
        $new_url .= "?identifier=$identifier";
        $new_url .= "&postal_code=$postal_code";
        $new_url .= "&omit_service_points=$omit_service_points";
        return self::sentRequest($new_url);
    }

    public static function deleteParcel(string $identifier)
    {
        $new_url = str_replace("{id}", $identifier, self::$detele_parcel);
        return self::sentRequest($new_url, true);
    }

    public static function createParcel(Order $order, int $id_parcel = 0)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;
        $customer = new Customer($order->id_customer);
        $address = new Address($order->id_address_delivery, $id_lang);
        $country = new Country($address->id_country, $id_lang, $id_shop);
        $currency = new Currency($order->id_currency, $id_lang, $id_shop);

        $parcel["parcel"] = [
            "name" => $customer->firstname . ' ' . $customer->lastname,
            "company_name" => $address->company,
            "address" => $address->address1,
            "address_2" => $address->address2,
            "house_number" => "0",
            "city" => $address->city,
            "postal_code" => $address->postcode,
            "telephone" => $address->phone == "" || is_null($address->phone) || $address->phone == null ? $address->phone_mobile : $address->phone,
            "email" => $customer->email,
            "country" => $country->iso_code,

            "total_order_value" => round($order->getOrdersTotalPaid(), 2),
            "total_order_value_currency" => $currency->iso_code,
            'order_number' => $order->reference,
            "quantity" => SendcloudOrder::getOrderQuantity($order->id),
            "weight" => SendcloudOrder::getOrderWeight($order->id),
            "reference" => $order->reference,

            "is_return" => false,
            "request_label" => false,
            "apply_shipping_rules" => false,
            "request_label_async" => false,

            /* "from_name" => $address->firstname . ' ' . $address->lastname,
                "from_company_name" => $address->company,
                "from_email" => $customer->email,
                "from_telephone" => $address->phone,
                "from_address_1" => $address->address1,
                "from_house_number" => "0",
                "from_address_2" => $address->address2,
                "from_city" => $address->city,
                "from_postal_code" => $address->postcode,
                "from_country" => $country->iso_code, */
        ];
        
        if ($id_parcel != 0) {
            $parcel["parcel"]["id"] = $id_parcel;
        }

        foreach (SendcloudOrder::getOrdersDetails($order->id) as $order_detail) {
            $parcel["parcel"]["parcel_items"][] = [
                "description" => $order_detail->product_name,
                "hs_code" => "",
                "item_id" => $order_detail->product_attribute_id,
                "product_id" => $order_detail->product_id,
                "quantity" => $order_detail->product_quantity,
                "sku" => $order_detail->product_reference.'@'.$order_detail->product_attribute_id,
                "value" => round($order_detail->total_price_tax_excl, 2),
                "weight" => round($order_detail->product_weight, 2) >= 0.00099 ? round($order_detail->product_weight, 2) : 0.01,
            ];
        }
        
        return self::sentRequest(self::$url_parcels, true, $parcel);
    }

    public static function getParcels(string $order_number, int $parcel_status)
    {
        $new_url = self::$url_parcels;
        $new_url .= "?order_number=$order_number";
        $new_url .= "&parcel_status=$parcel_status";
        return self::sentRequest($new_url);
    }

    public static function checkParcel(Order $order)
    {
        $address = new Address($order->id_address_delivery, $order->id_lang);
        $result = self::outgoingParcel($order->reference, $address->postcode);
        return (empty($result) || is_null($result)) ? false : $result["data"]["parcel"]["id"];
    }

    public static function getBrandDomaine()
    {
        return Configuration::get('SENDCLOUD_BRAND_DOMAINE');
    }

    public static function getUrlReturnsPortail()
    {
        return Configuration::get('SENDCLOUD_RETURN_PORTAIL_URL');
    }
}
