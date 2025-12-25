<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Laravel 11では unsignedFloat() が削除され、double() も精度パラメータを受け付けなくなった。
     * このマイグレーションは既存インストールの raspberry_pis テーブルを
     * decimal 型に変換し、新規インストールとのスキーマ整合性を保つ。
     */
    public function up(): void
    {
        if (!Schema::hasTable('raspberry_pis')) {
            return;
        }

        $columns = $this->getColumnTypes();

        // cpu_temperature が decimal でない場合のみ変更
        if (isset($columns['cpu_temperature']) && !$this->isDecimalType($columns['cpu_temperature'])) {
            Schema::table('raspberry_pis', function (Blueprint $table) {
                $table->decimal('cpu_temperature', 6, 1)
                    ->nullable()
                    ->comment(__('yokakit.cpu_temperature'))
                    ->change();
            });
        }

        // cpu_utilization が decimal でない場合のみ変更
        if (isset($columns['cpu_utilization']) && !$this->isDecimalType($columns['cpu_utilization'])) {
            Schema::table('raspberry_pis', function (Blueprint $table) {
                $table->decimal('cpu_utilization', 5, 1)
                    ->unsigned()
                    ->nullable()
                    ->comment(__('yokakit.cpu_utilization'))
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 注意: ロールバックは double 型に戻すが、Laravel 11 では動作しない。
     * Laravel 10 以前でのみロールバック可能。
     */
    public function down(): void
    {
        if (!Schema::hasTable('raspberry_pis')) {
            return;
        }

        // Laravel 11 では double() に精度パラメータを渡せないため、
        // 生SQL でロールバックを行う
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `raspberry_pis` MODIFY `cpu_temperature` DOUBLE(8,2) NULL COMMENT ?', [
                __('yokakit.cpu_temperature'),
            ]);
            DB::statement('ALTER TABLE `raspberry_pis` MODIFY `cpu_utilization` DOUBLE(8,2) UNSIGNED NULL COMMENT ?', [
                __('yokakit.cpu_utilization'),
            ]);
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "raspberry_pis" ALTER COLUMN "cpu_temperature" TYPE DOUBLE PRECISION');
            DB::statement('ALTER TABLE "raspberry_pis" ALTER COLUMN "cpu_utilization" TYPE DOUBLE PRECISION');
        }
    }

    /**
     * テーブルのカラム型情報を取得
     */
    private function getColumnTypes(): array
    {
        $driver = DB::getDriverName();
        $columns = [];

        if ($driver === 'mysql') {
            $results = DB::select("SHOW COLUMNS FROM `raspberry_pis` WHERE Field IN ('cpu_temperature', 'cpu_utilization')");
            foreach ($results as $row) {
                $columns[$row->Field] = $row->Type;
            }
        } elseif ($driver === 'pgsql') {
            $results = DB::select("
                SELECT column_name, data_type
                FROM information_schema.columns
                WHERE table_name = 'raspberry_pis'
                AND column_name IN ('cpu_temperature', 'cpu_utilization')
            ");
            foreach ($results as $row) {
                $columns[$row->column_name] = $row->data_type;
            }
        } elseif ($driver === 'sqlite') {
            $results = DB::select("PRAGMA table_info(raspberry_pis)");
            foreach ($results as $row) {
                if (in_array($row->name, ['cpu_temperature', 'cpu_utilization'])) {
                    $columns[$row->name] = strtolower($row->type);
                }
            }
        }

        return $columns;
    }

    /**
     * カラム型が decimal かどうかを判定
     */
    private function isDecimalType(string $type): bool
    {
        $type = strtolower($type);

        return str_contains($type, 'decimal') || str_contains($type, 'numeric');
    }
};
