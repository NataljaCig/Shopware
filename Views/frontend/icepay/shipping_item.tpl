{extends file="parent:frontend/checkout/change_payment.tpl"}

{block name='frontend_checkout_payment_content'}
    <style>
        .payment--method .active {
            border-color: #3b99fc;
            border-radius: 10px;
            margin-right: 20px;
            overflow: hidden;
        }
        .payment_img {
            border: 4px solid transparent;
            margin-right: 20px;
            height: 35px;
            float:left;
            overflow: visible;
        }
        .payment_item {
            float: left;
            margin-right: 20px;
        }
        .hidden {
            display: none;
        }
    </style>
    <div class="panel--body is--wide block-group" xmlns="http://www.w3.org/1999/html">
        {assign var=i value=0}
        {foreach $payments as $payment}
            <div class="payment--method">

                {* Radio Button *}
                {block name='frontend_checkout_payment_fieldset_input_radio'}
                    <div class="" style="display: inline-block;">
                        <input style="{if $payment->image}display: none;{/if}height: 39px;" type="radio" name="payment" class="js_showHideIssuers payment_item {if $i == 0}active{/if} payment_item{$payment->payment_code}" data-id="{$payment->payment_code}" value="{$payment->payment_code}" id="payment_method{$payment->payment_code}" {if $i == 0}checked="checked"{/if} />
                        <div data-id="{$payment->payment_code}" class="payment_img {if $i == 0}active{/if}{if !$payment->image}hidden{/if}" style=""><img src="{$uploadDir}{$payment->image}" /></div>
                        <label style="float:left; margin-top: 6px;" class="method--name is--strong" for="payment_mean{$payment->payment_code}">{$payment->name}</label>
                    </div>
                {/block}

                {* Method Name *}
                {block name='frontend_checkout_payment_fieldset_input_label'}
                {/block}

                {* Method Logo *}
                {block name='frontend_checkout_payment_fieldset_template'}
                    <div class="payment--method-logo payment_logo_{$payment->name}"></div>
                    <div class="method--bankdata is--hidden issuers_{$payment->payment_code}">
                        <div class="debit">
                            {foreach $payment->issuers as $issuer}
                                {if $issuer->payment_id == $payment->id}
                                    <div class="none" style="display: inline-block; width: 100%;">
                                        <input type="radio" required name="issuer" style="float:left; margin-right: 20px; margin-top: 8px;" class="" value="{$issuer->issuer_code}" style="margin-right: 10px;"/>
                                        <label for="issuer" style="float:left;">{$issuer->name}</label>
                                    </div>
                                {/if}
                            {/foreach}
                            {if $payment->isEmptyIssuers}
                                <div class="none" style="display: none; width: 100%;">
                                    <input type="radio" required name="issuer" style="float:left; margin-right: 20px; margin-top: 8px;" class="" value="DEFAULT" style="margin-right: 10px;"/>
                                    <label for="issuer" style="float:left;">{$payment->name}</label>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/block}
            </div>

            {assign var=i value=$i+1}
        {/foreach}
    </div>
{/block}