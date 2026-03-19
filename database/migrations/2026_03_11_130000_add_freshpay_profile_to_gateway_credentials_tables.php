<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateway_credentials', function (Blueprint $table) {
            $table->string('freshpay_firstname')->nullable()->after('freshpay_hmac_key');
            $table->string('freshpay_lastname')->nullable()->after('freshpay_firstname');
            $table->string('freshpay_email')->nullable()->after('freshpay_lastname');
            $table->string('freshpay_username')->nullable()->after('freshpay_email');
        });

        Schema::table('global_payment_gateway_credentials', function (Blueprint $table) {
            $table->string('freshpay_firstname')->nullable()->after('freshpay_hmac_key');
            $table->string('freshpay_lastname')->nullable()->after('freshpay_firstname');
            $table->string('freshpay_email')->nullable()->after('freshpay_lastname');
            $table->string('freshpay_username')->nullable()->after('freshpay_email');
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateway_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'freshpay_firstname',
                'freshpay_lastname',
                'freshpay_email',
                'freshpay_username',
            ]);
        });

        Schema::table('global_payment_gateway_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'freshpay_firstname',
                'freshpay_lastname',
                'freshpay_email',
                'freshpay_username',
            ]);
        });
    }
};
