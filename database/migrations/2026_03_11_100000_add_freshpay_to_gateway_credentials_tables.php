<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateway_credentials', function (Blueprint $table) {
            $table->enum('freshpay_status', ['active', 'deactive'])->default('deactive')->after('payfast_status');
            $table->enum('freshpay_mode', ['sandbox', 'live'])->default('sandbox')->after('freshpay_status');
            $table->string('freshpay_merchant_id')->nullable()->after('freshpay_mode');
            $table->text('freshpay_merchant_secret')->nullable()->after('freshpay_merchant_id');
            $table->string('test_freshpay_merchant_id')->nullable()->after('freshpay_merchant_secret');
            $table->text('test_freshpay_merchant_secret')->nullable()->after('test_freshpay_merchant_id');
            $table->text('freshpay_encryption_key')->nullable()->after('test_freshpay_merchant_secret');
            $table->text('freshpay_hmac_key')->nullable()->after('freshpay_encryption_key');
        });

        Schema::table('global_payment_gateway_credentials', function (Blueprint $table) {
            $table->enum('freshpay_status', ['active', 'deactive'])->default('deactive')->after('payfast_status');
            $table->enum('freshpay_mode', ['sandbox', 'live'])->default('sandbox')->after('freshpay_status');
            $table->string('freshpay_merchant_id')->nullable()->after('freshpay_mode');
            $table->text('freshpay_merchant_secret')->nullable()->after('freshpay_merchant_id');
            $table->string('test_freshpay_merchant_id')->nullable()->after('freshpay_merchant_secret');
            $table->text('test_freshpay_merchant_secret')->nullable()->after('test_freshpay_merchant_id');
            $table->text('freshpay_encryption_key')->nullable()->after('test_freshpay_merchant_secret');
            $table->text('freshpay_hmac_key')->nullable()->after('freshpay_encryption_key');
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateway_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'freshpay_status',
                'freshpay_mode',
                'freshpay_merchant_id',
                'freshpay_merchant_secret',
                'test_freshpay_merchant_id',
                'test_freshpay_merchant_secret',
                'freshpay_encryption_key',
                'freshpay_hmac_key',
            ]);
        });

        Schema::table('global_payment_gateway_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'freshpay_status',
                'freshpay_mode',
                'freshpay_merchant_id',
                'freshpay_merchant_secret',
                'test_freshpay_merchant_id',
                'test_freshpay_merchant_secret',
                'freshpay_encryption_key',
                'freshpay_hmac_key',
            ]);
        });
    }
};
