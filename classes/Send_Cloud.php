<?php

class Send_Cloud extends ObjectModel
{

    const TABLE_NAME = "sendcloud";
    /**
     * ID Order
     *
     * @var int
     */
    public $id_order;
    /**
     * ID Parcel
     *
     * @var int
     */
    public $id_parcel;
    /**
     * Reference Order
     *
     * @var string
     */
    public $order_number;
    /**
     * Is Update SendCloud With Api After Initialisation
     *
     * @var bool
     */
    public $update = 0;
    /**
     * Tracking Number
     *
     * @var string
     */
    public $tracking_number;
    /**
     * Tracking Url
     *
     * @var string
     */
    public $tracking_url;
    /**
     * Label Url
     *
     * @var string
     */
    public $label_printer;
    /**
     * Date Add
     *
     * @var string
     */
    public $date_add;
    /**
     * Date Update
     *
     * @var string
     */
    public $date_upd;

    public static $definition = [
        "table" => "sendcloud",
        "primary" => "id_sendcloud",
        "fields" => [
            "id_order" => [
                "type" => self::TYPE_INT,
                "validate" => "isInt",
                "required" => true
            ],
            "id_parcel" => [
                "type" => self::TYPE_INT,
                "validate" => "isInt"
            ],
            "order_number" => [
                "type" => self::TYPE_STRING,
                "validate" => "isString"
            ],
            "update" => [
                "type" => self::TYPE_BOOL,
                "validate" => "isBool"
            ],
            "update" => [
                "type" => self::TYPE_BOOL,
                "validate" => "isBool"
            ],
            "tracking_number" => [
                "type" => self::TYPE_STRING,
                "validate" => "isString"
            ],
            "tracking_url" => [
                "type" => self::TYPE_STRING,
                "validate" => "isString"
            ],
            "label_printer" => [
                "type" => self::TYPE_STRING,
                "validate" => "isString"
            ],
            "date_add" => [
                "type" => self::TYPE_DATE,
                "validate" => "isDate"
            ],
            "date_upd" => [
                "type" => self::TYPE_DATE,
                "validate" => "isDate"
            ],
        ]
    ];

    public static function isExist(int $id_parcel): bool
    {
        $q = new DbQuery();
        $q->select("a.id_parcel")
            ->from(self::TABLE_NAME, "a")
            ->where("a.id_parcel = $id_parcel");
        return Db::getInstance()->getValue($q) == false ? false : true;
    }

    public static function getSendclouds(int $update = 1): array
    {
        $q = new DbQuery();
        $q->select("a.id_sendcloud")
            ->from(self::TABLE_NAME, "a")
            ->where("a.update = $update");
        $results = Db::getInstance()->executeS($q);
        if (!empty($results) && !is_null($results) && $results) {
            return array_map(function ($a) {
                return new Send_Cloud((int)$a["id_sendcloud"]);
            }, $results);
        }
        return [];
    }

    public static function getSendcloudWithParcel(int $id_parcel)
    {
        $q = new DbQuery();
        $q->select("a.id_sendcloud")
            ->from(self::TABLE_NAME, "a")
            ->where("a.id_parcel = $id_parcel");
        $results = Db::getInstance()->getRow($q);
        if ($results && !empty($results) && !is_null($results)) {
            return new Send_Cloud((int)$results["id_sendcloud"]);
        }
        return null;
    }
}
