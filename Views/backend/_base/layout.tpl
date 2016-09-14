<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{link file="backend/_resources/css/bootstrap.min.css"}">
</head>
<body role="document" style="padding-top: 80px">

<!-- Fixed navbar -->
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li{if {controllerAction} === 'index'} class="active"{/if}><a href="{url controller="IcePayBackend" action="index"}">Payments</a></li>
                <li{if {controllerAction} === 'refreshPayments'} class="active"{/if}><a href="{url controller="IcePayBackend" action="refreshPayments"}">Update Icepay Payments</a></li>

            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container theme-showcase" role="main">
    {block name="content/main"}{/block}
</div> <!-- /container -->

<script type="text/javascript" src="{link file="backend/base/frame/postmessage-api.js"}"></script>
<!--<script type="text/javascript" src="{link file="backend/_base/frame/postmessage-api.js"}"></script>-->
<script type="text/javascript" src="{link file="backend/_resources/js/jquery-2.1.4.min.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/bootstrap.min.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/jquery-sortable.js"}"></script>
{block name="content/layout/javascript"}
<script type="text/javascript">
    $(function() {
        $('.title-form').on('submit', function(event) {
            var $this = $(this),
                values = $this.serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
            event.preventDefault();

            postMessageApi.window.setTitle(values.title);
        });

        $('.get-window-width-form').on('submit', function(event) {
            var $this = $(this),
                $display = $this.find('input[name="window-width"]');

            event.preventDefault();

            postMessageApi.window.getWidth(function(width) {
                $display.val(width + 'px');
            });
        });

        $('.set-window-width-form').on('submit', function(event) {
            var $this = $(this),
                values = $this.serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
            event.preventDefault();

            postMessageApi.window.setWidth(values['window-width']);
        });

        $('.get-window-height-form').on('submit', function(event) {
            var $this = $(this),
                $display = $this.find('input[name="window-height"]');

            event.preventDefault();

            postMessageApi.window.getHeight(function(width) {
                $display.val(width + 'px');
            });
        });

        $('.set-window-height-form').on('submit', function(event) {
            var $this = $(this),
                values = $this.serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
            event.preventDefault();

            postMessageApi.window.setHeight(values['window-height']);
        });

        $('.open-module-form').on('submit', function(event) {
            var $this = $(this),
                values = $this.serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
            event.preventDefault();

            postMessageApi.openModule({
                name: 'Shopware.apps.' + values['module-name']
            });
        });

        $('.open-subwindow-form').on('submit', function(event) {
            var $this = $(this),
                values = $this.serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
            event.preventDefault();

            values.width = 500;
            values.height = 500;

            postMessageApi.createSubWindow(values);
        });

        $('.btn-subwindow').on('click', function() {
            var values = {
                width: 500,
                height: 500,
                component: 'customSubWindow',
                url: 'IcePayBackend/create_sub_window',
                title: 'Plugin Konfiguration'
            };

            postMessageApi.createSubWindow(values);

            window.setTimeout(function() {
                postMessageApi.sendMessageToSubWindow({
                    component: values.component,
                    params: {
                        msg: 'A message from another galaxy beyond the sky.',
                        foo: [ 'bar', 'batz', 'foobar' ]
                    }
                });
            }, 3000);
        });

        $('.btn-minimize').on('click', function() {
           postMessageApi.window.minimize();
        });

        $('.btn-maximize').on('click', function() {
            postMessageApi.window.maximize();
        });

        $('.btn-show').on('click', function() {
            postMessageApi.window.show();
        });

        $('.btn-hide').on('click', function() {
            postMessageApi.window.hide();
        });

        $('.btn-destroy').on('click', function() {
            var response = confirm('Are you sure that you wanna destroy the module?');
            if(!response) {
                return;
            }
            postMessageApi.window.destroy();
        });
        $('#js_select_all').on('click', function() {
            $('.js_payments_checkbox, .js_issuers_checkbox').prop('checked', ($(this).prop('checked') == true));
        });
        $('.js_payments_checkbox').on('click', function() {
            var id = $(this).val();
            $('.issuers'+id).prop('checked', ($(this).prop('checked') == true));
        });
    });
    var oldContainer, oldContainer1;
    $(function  () {
        $(".sortable_payments").sortable({
            group: 'no-drop',
            handle: 'i.icon-move-payments',
            itemPath: '> .payments-block',
            itemSelector: '.sortable_li_payments',
            onDragStart: function ($item, container, _super) {
                if(!container.options.drop)
                    $item.clone().insertAfter($item);
                _super($item, container);
            },
            afterMove: function (placeholder, container) {
                if(oldContainer != container){
                    if(oldContainer)
                        oldContainer.el.removeClass("active");
                    container.el.addClass("active");

                    oldContainer = container;
                }
            },
            onDrop: function ($item, container, _super) {
                container.el.removeClass("active");
                _super($item, container);
            }
        });
        $(".sortable_issuers").sortable({
            group: 'no-drop',
            handle: 'i.icon-move-issuers',
            itemPath: '> .issuers-block',
            itemSelector: '.sortable_li_issuers',
            onDragStart: function ($item, container, _super) {
                if(!container.options.drop)
                    $item.clone().insertAfter($item);
                _super($item, container);
            },
            afterMove: function (placeholder, container) {
                if(oldContainer1 != container){
                    if(oldContainer1)
                        oldContainer1.el.removeClass("active");
                    container.el.addClass("active");

                    oldContainer1 = container;
                }
            },
            onDrop: function ($item, container, _super) {
                container.el.removeClass("active");
                _super($item, container);
            }
        });
    });
</script>
{/block}
{block name="content/javascript"}{/block}
</body>
</html>