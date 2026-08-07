<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportingFieldsToAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('action')->nullable()->after('description');
            $table->string('module')->nullable()->after('action');
            $table->string('actor_name')->nullable()->after('user_id');
            $table->string('actor_role')->nullable()->after('actor_name');
            $table->unsignedBigInteger('target_user_id')->nullable()->after('actor_role');
            $table->string('target_user_name')->nullable()->after('target_user_id');
            $table->string('subject_name')->nullable()->after('target_user_name');
        });
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'action',
                'module',
                'actor_name',
                'actor_role',
                'target_user_id',
                'target_user_name',
                'subject_name',
            ]);
        });
    }
}
