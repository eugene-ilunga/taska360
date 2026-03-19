<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FreshPay</title>
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f7efe2 0%, #f3f7fb 100%);
            font-family: Georgia, "Times New Roman", serif;
            color: #1f2933;
        }

        .freshpay-waiting-card {
            width: min(92vw, 640px);
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: 0 24px 80px rgba(31, 41, 51, 0.12);
        }

        .freshpay-badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            background: #fff4d6;
            color: #9a6700;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .freshpay-spinner {
            width: 54px;
            height: 54px;
            border: 4px solid #e6ecf2;
            border-top-color: #d97706;
            border-radius: 50%;
            animation: freshpay-spin 1s linear infinite;
            margin-bottom: 22px;
        }

        @keyframes freshpay-spin {
            to { transform: rotate(360deg); }
        }

        .freshpay-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .freshpay-actions a,
        .freshpay-actions button {
            border-radius: 999px;
            padding: 12px 18px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="freshpay-waiting-card text-center">
    <div class="freshpay-badge">FreshPay</div>
    <div class="d-flex justify-content-center">
        <div class="freshpay-spinner"></div>
    </div>

    <h2 class="mb-3">{{ __('modules.freshpay.pendingState') }}</h2>
    <p class="text-muted mb-3">
        @lang('modules.freshpay.pendingHelp')
    </p>

    <div id="freshpay-waiting-status" class="alert alert-warning mb-0">
        @lang('modules.freshpay.pendingState')
    </div>

    <div class="freshpay-actions justify-content-center">
        <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
            @lang('app.cancel')
        </a>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script>
    const pollUrl = @json($pollUrl);
    const returnUrl = @json($returnUrl);
    const statusBox = $('#freshpay-waiting-status');
    let pollTimer = null;

    function redirectBack(message, isSuccess) {
        sessionStorage.setItem('freshpay_callback_notice', JSON.stringify({
            message: message,
            is_success: isSuccess
        }));

        window.location.href = returnUrl;
    }

    function startWaitingPoll() {
        console.log('[FreshPay Waiting] Polling started', { poll_url: pollUrl, return_url: returnUrl });

        pollTimer = setInterval(function () {
            $.get(pollUrl, function (response) {
                console.log('[FreshPay Waiting] Polling response', response);
                statusBox
                    .removeClass('alert-warning alert-success alert-danger')
                    .addClass(response.payment_status === 'complete' ? 'alert-success' : (response.payment_status === 'failed' ? 'alert-danger' : 'alert-warning'))
                    .text(response.message);

                if (!response.is_final) {
                    return;
                }

                clearInterval(pollTimer);
                redirectBack(response.message, response.payment_status === 'complete');
            });
        }, 4000);
    }

    startWaitingPoll();
</script>
</body>
</html>
