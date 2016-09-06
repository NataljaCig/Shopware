{extends file="parent:frontend/checkout/shipping_payment.tpl"}

{block name='frontend_index_navigation_categories_top'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}

    {include file="{$routeToShippingStepsTpl}"}
{/block}

{block name="frontend_index_content"}
    <div class="content content--confirm product--table" data-ajax-shipping-payment="true">
        {block name='frontend_account_payment_error_messages'}
            {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
        {/block}

        <div class="confirm--outer-container">
            <form id="shippingPaymentForm" name="shippingPaymentForm" method="post" action="/frontend/icepay/process" class="payment">

                {block name='frontend_checkout_shipping_payment_core_buttons'}
                {/block}

                <div class="shipping-payment--information">
                    <div class="confirm--inner-container block">
                        {block name='frontend_checkout_shipping_payment_core_payment_fields'}
                            {include file="$routeToShippingItemTpl" }
                        {/block}
                    </div>
                </div>
            </form>

            {block name='frontend_checkout_shipping_payment_core_buttons'}
                <div class="confirm--actions table--actions block">
                    <button type="submit" form="shippingPaymentForm" class="btn is--primary is--icon-right is--large right main--actions">{s namespace='frontend/checkout/shipping_payment' name='NextButton'}{/s}<i class="icon--arrow-right"></i></button>
                </div>
            {/block}

            {block name="frontend_checkout_footer"}
                {include file="frontend/checkout/table_footer.tpl"}
            {/block}
        </div>
    </div>
{/block}

{block name="frontend_index_header_javascript_jquery" prepend}
    <script>
        $(document).ready(function(){
            $('.js_showHideIssuers').click(function () {
                var id = $(this).data('id');
                $('.payment_item').removeClass('active');
                $(this).addClass('active');
                $('input[name=issuer]:checked').prop('checked',false);
                var el = $(this).parent().parent().find('input[name=issuer]')[0];
                $(el).prop('checked', true);
                $('.method--bankdata:not(.is-hidden)').addClass('is--hidden');
                $('.issuers_'+id).removeClass('is--hidden');
                $('#payment_method'+id).prop('checked',true);
            });
            var selectedId = $('input[name=payment]:checked').data('id');
            var el = $('input[name=payment]:checked').parent().parent().find('input[name=issuer]')[0];
            $(el).prop('checked', true);
            $('.payment_item').removeClass('active');
            $('.payment_item'+selectedId).addClass('active');
            $('.issuers_'+selectedId).removeClass('is--hidden');
        });
    </script>
{/block}