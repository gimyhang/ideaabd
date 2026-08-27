<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('idea_employees')) {
            Schema::table('idea_employees', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_employees', 'employment_type')) {
                    $table->string('employment_type', 40)->default('monthly')->after('department');
                }
                if (!Schema::hasColumn('idea_employees', 'salary_rate_type')) {
                    $table->string('salary_rate_type', 40)->default('monthly')->after('basic_salary');
                }
                if (!Schema::hasColumn('idea_employees', 'rate_unit_name')) {
                    $table->string('rate_unit_name', 60)->nullable()->after('salary_rate_type');
                }
                if (!Schema::hasColumn('idea_employees', 'skill_category')) {
                    $table->string('skill_category', 100)->nullable()->after('employment_type');
                }
                if (!Schema::hasColumn('idea_employees', 'payment_schedule')) {
                    $table->string('payment_schedule', 40)->default('monthly')->after('rate_unit_name');
                }
            });
        }

        if (Schema::hasTable('idea_salary_payments')) {
            Schema::table('idea_salary_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_salary_payments', 'employment_type')) {
                    $table->string('employment_type', 40)->nullable()->after('salary_month');
                }
                if (!Schema::hasColumn('idea_salary_payments', 'work_details')) {
                    $table->text('work_details')->nullable()->after('payment_date');
                }
                if (!Schema::hasColumn('idea_salary_payments', 'job_quantity')) {
                    $table->decimal('job_quantity', 12, 2)->nullable()->after('work_details');
                }
                if (!Schema::hasColumn('idea_salary_payments', 'rate_per_unit')) {
                    $table->decimal('rate_per_unit', 12, 2)->nullable()->after('job_quantity');
                }
                if (!Schema::hasColumn('idea_salary_payments', 'rate_unit_name')) {
                    $table->string('rate_unit_name', 60)->nullable()->after('rate_per_unit');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idea_employees')) {
            Schema::table('idea_employees', function (Blueprint $table) {
                $columns = ['employment_type', 'salary_rate_type', 'rate_unit_name', 'skill_category', 'payment_schedule'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('idea_employees', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('idea_salary_payments')) {
            Schema::table('idea_salary_payments', function (Blueprint $table) {
                $columns = ['employment_type', 'work_details', 'job_quantity', 'rate_per_unit', 'rate_unit_name'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('idea_salary_payments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
