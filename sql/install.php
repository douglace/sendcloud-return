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
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'sendcloud` (
    `id_sendcloud` INT(11) NOT NULL AUTO_INCREMENT,
    `id_order` INT(11) NOT NULL,
    `id_parcel` INT(11) DEFAULT NULL,
    `order_number` VARCHAR(255) DEFAULT NULL,
    `update` INT(1) DEFAULT 0,
    `tracking_number` VARCHAR(255) DEFAULT NULL,
    `tracking_url` VARCHAR(255) DEFAULT NULL,
    `label_printer` VARCHAR(255) DEFAULT NULL,
    `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (`id_sendcloud`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'order_return_sendcloud` (
    `id_sendcloud` INT(11) NOT NULL,
    `id_order_return` INT(11) NOT NULL,
    `id_order_detail` INT(11) NOT NULL,
    `quantity` INT(11) DEFAULT 0,
    PRIMARY KEY  (`id_sendcloud`,`id_order_return`,`id_order_detail`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
