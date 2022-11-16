{extends file="page.tpl"}

{block name="page_title"}
    {l s='Liste des commandes' mod='sendcloud' d='Shop.Theme.Action'}
{/block}

{block name="page_content"}
    <p class="description">
        {l s="Vous trouverez ici vos commandes livrée depuis moins de " mod='sendcloud' d='Shop.Theme.Action'}{$SENDCLOUD_ORDER_RETURN_NB_DAYS}{l s=" jours" mod='sendcloud' d='Shop.Theme.Action'}
    </p>

    {if $orders}
        <table class="table table-striped table-bordered table-labeled col-6">
            <thead class="thead-default">
                <tr>
                    <th>{l s='Order reference' d='Shop.Theme.Checkout'}</th>
                    <th>{l s='Date de livraison' d='Shop.Theme.Checkout'}</th>
                    <th>{l s='' d='Shop.Theme.Checkout'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$orders item=order}
                    <tr>
                        <th scope="row"><a href="{$context_link->getModuleLink($module_name, "return", ["id_order" => $order->id])}">{$order->reference}</a></th>
                        <td><a href="{$context_link->getModuleLink($module_name, "return", ["id_order" => $order->id])}">{$order->delivery_date}</a></td>
                        <th scope="row"><a href="{$context_link->getModuleLink($module_name, "return", ["id_order" => $order->id])}">
                            {l s='Retourner un article de ma commande' d='Shop.Theme.Checkout'}
                        </a></th>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
{/block}