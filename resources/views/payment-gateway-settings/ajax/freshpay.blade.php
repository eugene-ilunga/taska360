<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
    @include('sections.password-autocomplete-hide')
    <input type="hidden" name="payment_method" value="freshpay">

    <div class="row">
        <div class="col-lg-12 mb-3">
            <x-forms.checkbox :fieldLabel="__('modules.freshpay.status')" fieldName="freshpay_status"
                fieldId="freshpay_status" fieldValue="active" fieldRequired="true"
                :checked="$credentials->freshpay_status == 'active'" />
        </div>
    </div>

    <div class="row @if ($credentials->freshpay_status == 'deactive') d-none @endif" id="freshpay_details">
        <div class="col-lg-12">
            <x-forms.select fieldId="freshpay_mode" :fieldLabel="__('app.selectEnvironment')" fieldName="freshpay_mode">
                <option value="sandbox" @selected($credentials->freshpay_mode == 'sandbox')>@lang('app.sandbox')</option>
                <option value="live" @selected($credentials->freshpay_mode == 'live')>@lang('app.live')</option>
            </x-forms.select>
        </div>

        <div class="col-lg-6 freshpay_live {{ $credentials->freshpay_mode == 'live' ? '' : 'd-none' }}">
            <x-forms.text :fieldLabel="__('modules.freshpay.merchantId')" fieldName="freshpay_merchant_id"
                fieldId="freshpay_merchant_id" :fieldValue="$credentials->freshpay_merchant_id" fieldRequired="true" />
        </div>
        <div class="col-lg-6 freshpay_live {{ $credentials->freshpay_mode == 'live' ? '' : 'd-none' }}">
            <x-forms.label class="mt-3" fieldId="freshpay_merchant_secret" :fieldLabel="__('modules.freshpay.merchantSecret')" fieldRequired="true" />
            <x-forms.input-group>
                <input type="password" name="freshpay_merchant_secret" id="freshpay_merchant_secret" class="form-control height-35 f-14"
                    value="{{ $credentials->freshpay_merchant_secret }}" autocomplete="off">
                <x-slot name="preappend">
                    <button type="button" data-toggle="tooltip" data-original-title="{{ __('messages.viewKey') }}"
                        class="btn btn-outline-secondary border-grey height-35 toggle-password"><i class="fa fa-eye"></i></button>
                </x-slot>
            </x-forms.input-group>
        </div>

        <div class="col-lg-6 freshpay_sandbox {{ $credentials->freshpay_mode == 'sandbox' ? '' : 'd-none' }}">
            <x-forms.text :fieldLabel="__('app.test') . ' ' . __('modules.freshpay.merchantId')" fieldName="test_freshpay_merchant_id"
                fieldId="test_freshpay_merchant_id" :fieldValue="$credentials->test_freshpay_merchant_id" fieldRequired="true" />
        </div>
        <div class="col-lg-6 freshpay_sandbox {{ $credentials->freshpay_mode == 'sandbox' ? '' : 'd-none' }}">
            <x-forms.label class="mt-3" fieldId="test_freshpay_merchant_secret" :fieldLabel="__('app.test') . ' ' . __('modules.freshpay.merchantSecret')" fieldRequired="true" />
            <x-forms.input-group>
                <input type="password" name="test_freshpay_merchant_secret" id="test_freshpay_merchant_secret" class="form-control height-35 f-14"
                    value="{{ $credentials->test_freshpay_merchant_secret }}" autocomplete="off">
                <x-slot name="preappend">
                    <button type="button" data-toggle="tooltip" data-original-title="{{ __('messages.viewKey') }}"
                        class="btn btn-outline-secondary border-grey height-35 toggle-password"><i class="fa fa-eye"></i></button>
                </x-slot>
            </x-forms.input-group>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3" fieldId="freshpay_encryption_key" :fieldLabel="__('modules.freshpay.encryptionKey')" />
            <x-forms.input-group>
                <input type="password" name="freshpay_encryption_key" id="freshpay_encryption_key" class="form-control height-35 f-14"
                    value="{{ $credentials->freshpay_encryption_key }}" autocomplete="off">
                <x-slot name="append">
                    <button type="button" class="btn btn-outline-secondary border-grey height-35 generate-freshpay-key" data-target="#freshpay_encryption_key" data-length="32">
                        @lang('app.generate')
                    </button>
                </x-slot>
            </x-forms.input-group>
        </div>
        <div class="col-lg-6">
            <x-forms.label class="mt-3" fieldId="freshpay_hmac_key" :fieldLabel="__('modules.freshpay.hmacKey')" />
            <x-forms.input-group>
                <input type="password" name="freshpay_hmac_key" id="freshpay_hmac_key" class="form-control height-35 f-14"
                    value="{{ $credentials->freshpay_hmac_key }}" autocomplete="off">
                <x-slot name="append">
                    <button type="button" class="btn btn-outline-secondary border-grey height-35 generate-freshpay-key" data-target="#freshpay_hmac_key" data-length="64">
                        @lang('app.generate')
                    </button>
                </x-slot>
            </x-forms.input-group>
        </div>

        <div class="col-lg-4">
            <x-forms.text :fieldLabel="__('modules.freshpay.firstName')" fieldName="freshpay_firstname"
                fieldId="freshpay_firstname" :fieldValue="$credentials->freshpay_firstname ?: 'ZAA'" />
        </div>
        <div class="col-lg-4">
            <x-forms.text :fieldLabel="__('modules.freshpay.lastName')" fieldName="freshpay_lastname"
                fieldId="freshpay_lastname" :fieldValue="$credentials->freshpay_lastname ?: 'ZAA'" />
        </div>
        <div class="col-lg-4">
            <x-forms.text :fieldLabel="__('modules.freshpay.email')" fieldName="freshpay_email"
                fieldId="freshpay_email" :fieldValue="$credentials->freshpay_email ?: 'kasisrael@gmail.com'" />
        </div>
        <div class="col-lg-4">
            <x-forms.text :fieldLabel="__('modules.freshpay.username')" fieldName="freshpay_username"
                fieldId="freshpay_username" :fieldValue="$credentials->freshpay_username ?: 'zaa.israel191'" />
        </div>

        <div class="col-lg-12">
            <x-forms.label fieldId="" :fieldLabel="__('app.webhook')" class="mt-3" />
            <p class="text-bold">
                <span id="freshpay-webhook-link-text">{{ $webhookRoute }}</span>
                <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                    data-clipboard-target="#freshpay-webhook-link-text">
                    <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
            </p>
            <p class="text-primary">@lang('modules.freshpay.callbackHelp')</p>
        </div>
    </div>
</div>
<div class="w-100 border-top-grey">
    <x-setting-form-actions>
        <div class="d-flex">
            <x-forms.button-primary class="mr-3 w-100" icon="check" id="save_freshpay_data">@lang('app.save')
            </x-forms.button-primary>
        </div>
    </x-setting-form-actions>
</div>
<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
<script>
    $('body').on('change', '#freshpay_mode', function () {
        if ($(this).val() === 'live') {
            $('.freshpay_live').removeClass('d-none');
            $('.freshpay_sandbox').addClass('d-none');
        } else {
            $('.freshpay_live').addClass('d-none');
            $('.freshpay_sandbox').removeClass('d-none');
        }
    });

    $('body').off('click', '.generate-freshpay-key').on('click', '.generate-freshpay-key', function () {
        const target = $($(this).data('target'));
        const length = parseInt($(this).data('length'), 10);
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let key = '';

        for (let i = 0; i < length; i++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        target.attr('type', 'text').val(key);
    });

    $('#save_freshpay_data').click(function () {
        $.easyAjax({
            url: "{{ $updateRoute }}",
            container: '#editSettings',
            blockUI: true,
            type: 'PUT',
            disableButton: true,
            buttonSelector: '#save_freshpay_data',
            data: $('#editSettings').serialize()
        });
    });

    var clipboard = new ClipboardJS('.btn-copy');
    clipboard.on('success', function () {
        Swal.fire({
            icon: 'success',
            text: '@lang("app.webhookUrlCopied")',
            toast: true,
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: { confirmButton: 'btn btn-primary' },
            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
        });
    });
</script>
