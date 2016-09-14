{extends file="parent:backend/_base/layout.tpl"}



{block name="content/main"}
    <style>
        body.dragging, body.dragging * {
            cursor: move !important;
        }

        .dragged {
            position: absolute;
            opacity: 0.5;
            z-index: 2000;
        }

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

    <form method="post" class="ice-pay-form" enctype= multipart/form-data action="/backend/IcePayBackend/updateCustomPaymets">
        <ul style="list-style: none; padding-left: 0px;" class="sortable_payments">
            <li style="list-style: none; border-bottom: 1px solid #afafaf; width: 100%; display:inline-block;">
                <div style="text-align:left; float:left; width:10px;">
                    <input type="checkbox" id="js_select_all"/>
                </div>
                <div style="text-align: left; float:left;">Name</div>
            </li>

            <div class="payments-block">
            {assign var=i value=0}
            {foreach $payments as $payment}
                <li style="list-style: none; display: inline-block; width: 100%;" class="sortable_li_payments">
                    <i class="icon-move-payments" style="width: 20px; height: 20px; display: block; float: left; margin: 5px; background: black;"></i>
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
                    <div style="float:left;">
                        <input name="PaymentImage[]" type="file" />
                    </div>
                    <div style="float:left;"><img src="{$uploadDir}{$payment->image}" height="20px;"/></div>
                    <div style="clear: both;"></div>
                    <ul style="list-style: none; border-bottom: 1px solid #afafaf;"  class="sortable_issuers" >
                        <div class="issuers-block">
                            {foreach $issuers as $issuer}
                                {if $issuer->payment_id == $payment->id}
                                    <li style="list-style: none; display: inline-block; width: 100%;"  class="sortable_li_issuers">
                                        <i class="icon-move-issuers" style="width: 20px; height: 20px; display: block; float: left; margin: 5px; background: black;"></i>
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
                        </div>
                    </ul>
                    {assign var=i value=$i+1}
                </li>
            {/foreach}
            </div>
        </ul>
        <input type="submit" class="btn btn-submit" value="Save">
    </form>
{/block}

