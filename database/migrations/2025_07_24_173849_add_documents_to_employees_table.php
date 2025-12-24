<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('cv')->nullable()->after('signature_image');
            $table->string('offer_letter')->nullable()->after('cv');
            $table->string('aadhaar_front')->nullable()->after('offer_letter');
            $table->string('aadhaar_back')->nullable()->after('aadhaar_front');
            $table->string('pan_card')->nullable()->after('aadhaar_back');
            $table->string('marksheet')->nullable()->after('pan_card');
            $table->string('certificate')->nullable()->after('marksheet');
            $table->string('passbook')->nullable()->after('certificate');
            $table->string('photo')->nullable()->after('passbook');
            $table->string('other_document')->nullable()->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'cv',
                'offer_letter',
                'aadhaar_front',
                'aadhaar_back',
                'pan_card',
                'marksheet',
                'certificate',
                'passbook',
                'photo',
                'other_document',
            ]);
        });
    }
};
