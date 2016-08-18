{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_content"}
    <div class="content-main--inner">
    </div>
{/block}

{block name="frontend_index_content_wrapper"}
    <div class="content--wrapper">
        <div class="content checkout--content finish--content">
            <div class="finish--teaser panel has--border is--rounded">
                <h2 class="is--align-center panel--title teaser--title">Your payment is pending</h2>
                <div class="panel--body is--wide is--align-center">
                    <p class="teaser--text">{$pendingText}</p>
                    <p class="teaser--actions">
                        <a href="{$backToShopUrl}" class="btn is--secondary teaser--btn-back is--icon-left" title="{$backToShopTitle}"><i class="icon--arrow-left"></i><font><font>{$backToShopTitle}</font></font></a>
                        <a href="/checkout/cart" class="btn is--primary teaser--btn-back" title="{$backToCheckout}"><font><font>{$backToCheckout}</font></font></a>
                        <a href="/checkout/shippingPayment/sTarget/checkout" class="btn is--primary teaser--btn-back" title="{$backToChangePayment}"><font><font>{$backToChangePayment}</font></font></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name="frontend_index_content_left"}
{/block}