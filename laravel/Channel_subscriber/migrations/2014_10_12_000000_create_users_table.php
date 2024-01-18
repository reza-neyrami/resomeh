<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('CREATE TABLE users (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL ,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(100) NULL,
        enabled_notification_service ENUM(\'email\', \'sms\', \'telegram\') NOT NULL DEFAULT \'email\',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PARTITION BY RANGE (id) (
        PARTITION p0 VALUES LESS THAN (100000),
        PARTITION p1 VALUES LESS THAN (200000),
        PARTITION p2 VALUES LESS THAN (300000),
        PARTITION p3 VALUES LESS THAN (400000),
        PARTITION p4 VALUES LESS THAN (500000),
        PARTITION p5 VALUES LESS THAN MAXVALUE
    );');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
