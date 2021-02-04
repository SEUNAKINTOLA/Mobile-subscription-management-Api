<?php

use App\Models\Device;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Device::TABLE, function (Blueprint $table) {
            $table->id();
            $table->uuid(Device::COLUMN_CLIENT_TOKEN)
                ->nullable(false);
            $table->integer(Device::COLUMN_U_ID, false, true)
                ->nullable(false);
            $table->integer(Device::COLUMN_APP_ID, false, true)
                ->nullable(false);
            $table->string(Device::COLUMN_LANG, '25')
                ->nullable(false);
            $table->enum(Device::COLUMN_OS, Device::OSES)
                ->nullable(false);
            $table->timestamps();

            $uniqueClientTokenIndex = 'u_' . Device::TABLE . '_' . Device::COLUMN_CLIENT_TOKEN;
            $table->unique(
                [
                    Device::COLUMN_CLIENT_TOKEN
                ],
                $uniqueClientTokenIndex
            );

            $uniqueUserAppIndex = 'u_' . Device::TABLE . '_' . Device::COLUMN_U_ID . '_' . Device::COLUMN_APP_ID;
            $table->unique(
                [
                    Device::COLUMN_U_ID,
                    Device::COLUMN_APP_ID
                ],
                $uniqueUserAppIndex
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Device::TABLE);
    }
}
