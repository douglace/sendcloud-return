{extends file="page.tpl"}

{* {block name="page_title"}
    {l s='Commencez votre retour' mod='sendcloud' d='Shop.Theme.Action'}
{/block}

{block name="page_content"}
    <p class="description">
        {l s='Renseignez votre code postal, le numéro de commande et retournez votre commande en quelques étapes!' mod='sendcloud' d='Shop.Theme.Action'}
    </p>

    <div class="return-container">
        <form class="col-md-6 col-sm-12">
            <div class="form-group">
                <label for="postal_code">{l s='Code postal' mod='sendcloud' d='Shop.Theme.Action'}</label>
                <input type="text" class="form-control" id="postal_code" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="order_reference">{l s='Numéro de commande' mod='sendcloud' d='Shop.Theme.Action'}</label>
                <input type="text" class="form-control" id="order_reference" required autocomplete="off" value="{$order->reference}" disabled>
            </div>
            <div class="form-group">
                <input type="submit" class="form-control btn btn-primary" name="" id="order_reference">
            </div>
        </form>
    </div>

{/block} *}