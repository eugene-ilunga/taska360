<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">FreshPay</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="freshpayDetails" method="POST" class="ajax-form" action="{{ route('freshpay_public', [$id, $company->hash]) }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text :fieldLabel="__('app.amount')" fieldName="display_amount" fieldId="display_amount"
                            :fieldValue="currency_format($amount, $currencyId)" :fieldRequired="false" fieldReadOnly="true" />
                    </div>
                    <div class="col-lg-12">
                        <x-forms.tel :fieldLabel="__('modules.freshpay.phone')" fieldName="phone" fieldId="phone"
                            :fieldPlaceholder="__('modules.freshpay.phonePlaceholder')" fieldRequired="true" />
                    </div>
                    <div class="col-lg-12">
                        <x-forms.select fieldId="operator" :fieldLabel="__('modules.freshpay.operator')" fieldName="operator" fieldRequired="true">
                            @foreach (\App\Services\FreshPayService::METHODS as $method)
                                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-12">
                        <div class="alert alert-info mb-0">@lang('modules.freshpay.pendingHelp')</div>
                    </div>
                </div>
            </div>
        </x-form>
        <div id="freshpay-status-box" class="d-none mt-3 alert alert-warning"></div>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-freshpay-detail" icon="check">@lang('app.send')</x-forms.button-primary>
</div>

<script>
    let freshPayPollingTimer = null;

    function startFreshPayPolling(pollUrl, redirectUrl) {
        const statusBox = $('#freshpay-status-box');
        statusBox.removeClass('d-none alert-danger alert-success').addClass('alert-warning')
            .text('@lang("modules.freshpay.pendingState")');

        console.log('[FreshPay] Polling started', {
            poll_url: pollUrl,
            redirect_url: redirectUrl
        });

        freshPayPollingTimer = setInterval(function () {
            $.get(pollUrl, function (response) {
                console.log('[FreshPay] Polling response', response);
                statusBox.text(response.message);

                if (!response.is_final) {
                    return;
                }

                console.log('[FreshPay] Final status received', {
                    payment_status: response.payment_status,
                    callback_processed: response.callback_processed,
                    source: response.source,
                    reference: response.reference,
                    transaction_id: response.transaction_id,
                    message: response.message
                });

                clearInterval(freshPayPollingTimer);
                statusBox.removeClass('alert-warning').addClass(response.payment_status === 'complete' ? 'alert-success' : 'alert-danger');

                setTimeout(function () {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                        return;
                    }

                    window.location.reload();
                }, 1200);
            });
        }, 4000);
    }

    $('#save-freshpay-detail').click(function () {
        $.easyAjax({
            container: '#freshpayDetails',
            buttonSelector: '#save-freshpay-detail',
            disableButton: true,
            blockUI: true,
            type: 'POST',
            url: "{{ route('freshpay_public', [$id, $company->hash]) }}",
            data: $('#freshpayDetails').serialize(),
            success: function (response) {
                console.log('[FreshPay] Initiate response', response);

                if (response.status !== 'success') {
                    console.error('[FreshPay] Initiate failed', response);
                    return;
                }

                $('#save-freshpay-detail').addClass('d-none');
                startFreshPayPolling(response.poll_url, response.redirect_url);
            },
            error: function (xhr) {
                console.error('[FreshPay] HTTP error during initiate', {
                    status: xhr.status,
                    responseText: xhr.responseText
                });
            }
        });
    });
</script>
