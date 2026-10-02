<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Multi-tenancy: a client-role user belongs to one client company.
            // Nullable because super admins have no client affiliation.
            $table->foreignId('client_id')->nullable()->after('id')->index();

            // Security tracking
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->ipAddress('last_login_ip')->nullable()->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'client_id',
                'password_changed_at',
                'last_login_ip',
                'last_login_at',
            ]);
        });
    }
};
