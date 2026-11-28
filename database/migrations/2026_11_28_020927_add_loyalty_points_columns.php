<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('points')->default(0);
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'points_used')) {
                    $table->unsignedInteger('points_used')->default(0);
                }
                if (!Schema::hasColumn('orders', 'points_earned')) {
                    $table->unsignedInteger('points_earned')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('points');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'points_used')) {
                    $table->dropColumn('points_used');
                }
                if (Schema::hasColumn('orders', 'points_earned')) {
                    $table->dropColumn('points_earned');
                }
            });
        }
    }
};