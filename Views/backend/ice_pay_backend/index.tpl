{extends file="parent:backend/_base/layout.tpl"}



{block name="content/main"}
    <style>
        .ice-pay-form div {
            margin: 5px;
        }
        .ice-pay-form .name {
            width: 400px;
        }
        .ice-pay-form .even {
            background: rgba(0, 223, 255, 0.27);
        }
        .ice-pay-form table {
            width: 100%;
        }
        .ice-pay-form td {
            width: 33%;
        }
        .issuers tr > td {
            text-align: left;
        }
        .ice-pay-form tr > td:last-child, .issuers tr > td:last-child {
            text-align: right;
        }
    </style>
    <div class="page-header">
        <h1>Ice Pay Payments</h1>
    </div>

    <form method="post" class="ice-pay-form" action="/backend/IcePayBackend/updateCustomPaymets">
        <ul style="padding-left: 0px;">
            <li style="border-bottom: 1px solid #afafaf; width: 100%; display:inline-block;">
                <div style="text-align:left; float:left; width:10px;">
                    <input type="checkbox" id="js_select_all"/>
                </div>
                <div style="text-align: left; float:left;">Name</div>
            </li>

            {assign var=i value=0}
            {foreach $payments as $payment}
            <li style="display: inline-block; width: 100%;" {if $i%2==0}class="even"{else}class="odd"{/if}>
                <div style="float:left;">
                    <input name="PaymentState[]" class="js_payments_checkbox" value="{$payment->id}" type="checkbox" {if $payment->state}checked{/if}/>
                </div>
                <div style="float:left;">
                    <input name="PaymentCode[]" value="{$payment->payment_code}" type="hidden"/>
                    <input class="name" name="PaymentName[]" value="{$payment->name}" type="text"/>
                </div>
                <div style="float:left;">
                    <input name="PaymentPosition[]" value="{$payment->position}" type="text"/>
                </div>
                <div style="clear: both;"></div>
                <ul style="border-bottom: 1px solid #afafaf;" >
                    {foreach $issuers as $issuer}
                        {if $issuer->payment_id == $payment->id}
                            <li style="display: inline-block; width: 100%;">
                                <div style="float:left;">
                                    <input name="IssuerState[]" class="js_issuers_checkbox issuers{$payment->id}" value="{$issuer->id}" type="checkbox" {if $issuer->state}checked{/if}/>
                                </div>
                                <div style="margin-left:50px; float:left;">
                                    <input class="name" name="IssuerName[]" value="{$issuer->name}" type="text"/>
                                    <input name="IssuerCode[]" value="{$issuer->issuer_code}" type="hidden"/>
                                </div>
                                <div style="float:left;">
                                   <input name="IssuerPosition[]" value="{$issuer->position}" type="text"/>
                                </div>
                                <div style="clear: both;"></div>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
                {assign var=i value=$i+1}
            </li>
            {/foreach}
        </ul>
        <input type="submit" class="btn btn-submit" value="Save">
    </form>
{/block}