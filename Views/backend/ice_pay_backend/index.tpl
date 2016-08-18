{extends file="parent:backend/_base/layout.tpl"}

{block name="content/main"}
    <div class="page-header">
        <h1>Ice Pay Payments</h1>
    </div>
    <form method="post" action="/backend/IcePayBackend/updateCustomPaymets">
        <ul>
            {foreach $payments as $payment}
                <li>
                    <input name="PaymentCode[]" value="{$payment->payment_code}" type="hidden"/>
                    <input name="PaymentName[]" value="{$payment->name}" type="text"/>
                    <input name="PaymentPosition[]" value="{$payment->position}" type="text"/>
                    <input name="PaymentState[]" value="{$payment->id}" type="checkbox" {if $payment->state}checked{/if}/>
                </li>
                <ul>
                    {foreach $issuers as $issuer}
                        {if $issuer->payment_id == $payment->id}
                            <li>
                                <input name="IssuerCode[]" value="{$issuer->issuer_code}" type="hidden"/>
                                <input name="IssuerName[]" value="{$issuer->name}" type="text"/>
                                <input name="IssuerPosition[]" value="{$issuer->position}" type="text"/>
                                <input name="IssuerState[]" value="{$issuer->id}" type="checkbox" {if $issuer->state}checked{/if}/>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            {/foreach}
        </ul>
        <input type="submit" value="Save">
    </form>
{/block}