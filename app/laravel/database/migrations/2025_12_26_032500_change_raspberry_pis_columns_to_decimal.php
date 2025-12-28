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
                    ->comment('CPU Temperature')
                    ->change();
            });
        }

        // cpu_utilization が decimal でない場合のみ変更
        if (isset($columns['cpu_utilization']) && !$this->isDecimalType($columns['cpu_utilization'])) {
            Schema::table('raspberry_pis', function (Blueprint $table) {
                $table->decimal('cpu_utilization', 5, 1)
                    ->unsigned()
                    ->nullable()
                    ->comment('CPU Utilization')
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 注意: ロールバックは元の double 型に戻す。
     * Laravel 11 では double() に精度パラメータを渡せないため、生SQL を使用。
     * 元のスキーマ: cpu_temperature DOUBLE(8,2), cpu_utilization DOUBLE(8,2) UNSIGNED
     */
    public function down(): void
    {
        if (!Schema::hasTable('raspberry_pis')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `raspberry_pis` MODIFY `cpu_temperature` DOUBLE(8,2) NULL COMMENT ?', [
                'CPU Temperature',
            ]);
            DB::statement('ALTER TABLE `raspberry_pis` MODIFY `cpu_utilization` DOUBLE(8,2) UNSIGNED NULL COMMENT ?', [
                'CPU Utilization',
            ]);
        } elseif ($driver === 'pgsql') {
            // PostgreSQL では UNSIGNED 制約がないため、型のみ変更
            DB::statement('ALTER TABLE "raspberry_pis" ALTER COLUMN "cpu_temperature" TYPE DOUBLE PRECISION');
            DB::statement('ALTER TABLE "raspberry_pis" ALTER COLUMN "cpu_temperature" DROP NOT NULL');
            DB::statement('COMMENT ON COLUMN "raspberry_pis"."cpu_temperature" IS \'CPU Temperature\'');
            DB::statement('ALTER TABLE "raspberry_pis" ALTER COLUMN "cpu_utilization" TYPE DOUBLE PRECISION');
            DB::statement('ALTER TABLE "raspberry_pis" ALTER COLUMN "cpu_utilization" DROP NOT NULL');
            DB::statement('COMMENT ON COLUMN "raspberry_pis"."cpu_utilization" IS \'CPU Utilization\'');
        } elseif ($driver === 'sqlite') {
            // SQLite ではカラム型の変更にテーブル再作成が必要だが、
            // Schema::table の change() で対応可能
            Schema::table('raspberry_pis', function (Blueprint $table) {
                $table->double('cpu_temperature')->nullable()->change();
                $table->double('cpu_utilization')->nullable()->change();
            });
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
