{extends file="parent:frontend/checkout/change_payment.tpl"}

{block name='frontend_checkout_payment_content'}
    <style>
        .active {
            float: left;
            vertical-align: middle;
            margin-right: 20px;
            border-radius: 8px;
            background: #3b99fc;
        }
        .active img {
            vertical-align: middle;
            float: left;
            margin-right: 4px;
            margin-top: 4px;
            margin-left: 4px;
        }
        .payment_item {
            float: left;
            margin-right: 20px;
        }
    </style>
    <div class="panel--body is--wide block-group" xmlns="http://www.w3.org/1999/html">
        {assign var=i value=0}
        {foreach $payments as $payment}
            <div class="payment--method">

                {* Radio Button *}
                {block name='frontend_checkout_payment_fieldset_input_radio'}
                    <div class="" style="display: inline-block;">
                        <input style ="height:40px;" type="radio" name="payment" class="js_showHideIssuers payment_item {if $i == 0}active{/if} payment_item{$payment->payment_code}" data-id="{$payment->payment_code}" value="{$payment->payment_code}" id="payment_method{$payment->payment_code}" {if $i == 0}checked="checked"{/if} />
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