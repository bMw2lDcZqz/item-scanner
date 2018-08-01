<!-- Item Scanner Modal -->
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Begin scanning items...</h3>

                <div class="input-group">
                    <input type="text" id="scannedBarcode" name="scannedBarcode" data-bind="scannedBarcode: scannedBarcode, items: $root.products, display: 'manufacturer_product_details.gtin', key: 'manufacturer_product_details.gtin', valueUpdate: 'afterkeydown'" class="form-control"/>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" >Go!</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var itemScannerModel = {
       scannedBarcode: ko.observable('')
    };

    Mousetrap.bind('s', function(e) {
        event.preventDefault();
        $('#scanModal').modal('show');
    });

    // $('#scanModal').on('show.bs.modal', function () {
    //     $('#scannedBarcode').val("");
    // })

    $('#scanModal').on('shown.bs.modal', function () {
        $('#scannedBarcode').focus();
    })

    // $('#scanModal').on('hidden.bs.modal', function () {
    //     $('#scannedBarcode').val("");
    // })

    $("#scannedBarcode").keypress(function(e) {
        var c = e.keyCode;

        if((c >= 48 && c <= 57) || (c >= 65 && c <= 90) || (c >= 97 && c <= 122)) {
            this.value += e.key;
            e.preventDefault();
        } else if(c == 13) {
            console.log("enter key pressed");
            e.preventDefault();
        } else {
            e.preventDefault();
        }
    });

ko.bindingHandlers.scannedBarcode = {
    init: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var $element = $(element);
        var allBindings = allBindingsAccessor();
        console.log(allBindings);
        $element.typeahead({
            highlight: true,
            minLength: 0,
        },
        {
            name: 'data',
            display: allBindings.key,
            limit: 50,
            templates: {
                suggestion: function(item) { return '<div title="' + _.escape(item.notes) + '" style="border-bottom: solid 1px #CCC">'
                    + _.escape(item.product_key) + "<br/>"
                    + roundToTwo(item.cost, true) + ' • '
                    + _.escape(item.notes.substring(0, 100)) + '</div>' }
            },
            source: searchData(allBindings.items, allBindings.key, false, 'notes')
        }).on('typeahead:select', function(element, datum, name) {
            @if (Auth::user()->account->fill_products)
                var model = ko.dataFor(this);
                if (model.expense_public_id()) {
                    return;
                }
                if (datum.notes && (! model.notes() || ! model.isTask())) {
                    model.notes(datum.notes);
                }
                if (parseFloat(datum.cost)) {
                    if (! NINJA.parseFloat(model.cost()) || ! model.isTask()) {
                        var cost = datum.cost;

                        // optionally handle curency conversion
                        @if ($account->convert_products)
                            var rate = false;
                            if ((account.custom_fields.invoice_text1 || '').toLowerCase() == "{{ strtolower(trans('texts.exchange_rate')) }}") {
                                rate = window.model.invoice().custom_text_value1();
                            } else if ((account.custom_fields.invoice_text2 || '').toLowerCase() == "{{ strtolower(trans('texts.exchange_rate')) }}") {
                                rate = window.model.invoice().custom_text_value2();
                            }
                            if (rate) {
                                cost = cost * rate;
                            } else {
                                var client = window.model.invoice().client();
                                if (client) {
                                    var clientCurrencyId = client.currency_id();
                                    var accountCurrencyId = {{ $account->getCurrencyId() }};
                                    if (clientCurrencyId && clientCurrencyId != accountCurrencyId) {
                                        cost = fx.convert(cost, {
                                            from: currencyMap[accountCurrencyId].code,
                                            to: currencyMap[clientCurrencyId].code,
                                        });
                                        var rate = fx.convert(1, {
                                            from: currencyMap[accountCurrencyId].code,
                                            to: currencyMap[clientCurrencyId].code,
                                        });
                                        if ((account.custom_fields.invoice_text1 || '').toLowerCase() == "{{ strtolower(trans('texts.exchange_rate')) }}") {
                                            window.model.invoice().custom_text_value1(roundToFour(rate, true));
                                        } else if ((account.custom_fields.invoice_text2 || '').toLowerCase() == "{{ strtolower(trans('texts.exchange_rate')) }}") {
                                            window.model.invoice().custom_text_value2(roundToFour(rate, true));
                                        }
                                    }
                                }
                            }
                        @endif

                        model.cost(roundToTwo(cost, true));
                    }
                }
                if (! model.qty() && ! model.isTask()) {
                    model.qty(1);
                }
                @if ($account->invoice_item_taxes)
                    if (datum.tax_name1) {
                        var $select = $(this).parentsUntil('tbody').find('select').first();
                        $select.val('0 ' + datum.tax_rate1 + ' ' + datum.tax_name1).trigger('change');
                    }
                    if (datum.tax_name2) {
                        var $select = $(this).parentsUntil('tbody').find('select').last();
                        $select.val('0 ' + datum.tax_rate2 + ' ' + datum.tax_name2).trigger('change');
                    }
                @endif
                @if (Auth::user()->isPro() && $account->customLabel('product1'))
                    if (datum.custom_value1) {
                        model.custom_value1(datum.custom_value1);
                    }
                @endif
                @if (Auth::user()->isPro() && $account->customLabel('product2'))
                    if (datum.custom_value2) {
                        model.custom_value2(datum.custom_value2);
                    }
                @endif
            @endif
            onItemChange();
        }).on('typeahead:change', function(element, datum, name) {
            var value = valueAccessor();
            value(datum);
            onItemChange();
            refreshPDF(true);
        });
    },

    update: function (element, valueAccessor) {
        var value = ko.utils.unwrapObservable(valueAccessor());
        if (value) {
            $(element).typeahead('val', value);
        }
    }
};

    // $(function() {
    //     var itemModel = new ItemModel();
    //     itemModel.product_key("NP33-dd");
    //     itemModel.notes("")
    //     window.invoice.invoice_items_without_tasks.push(itemModel);
    //     applyComboboxListeners();
    // });
</script>
