<?php

namespace Admin\Http\Controllers\Api;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class UpdateRecipe
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $token = option('new_version_update_token');
        Artisan::call('migrate --force');

        Cache::flush();

        if (str_replace('.', '', option('app_updates')['version']) < 313) {
            $this->v313();
        }

        option([
            'app_updates' => [
                'update_available' => false,
                'version' => option('app_updates')['new_version'],
                'new_version' => option('app_updates')['new_version'],
                'checked_at' => option('app_updates')['checked_at'],
            ],
            'global_update_notification' => null,
            'new_version_update_token' => null,
        ]);

        Http::post('https://spack-admin.codedott.com/api/installation/status', [
            'success' => true,
            'token' => $token,
        ]);

        return [
            'success' => true,
            'status' => 'done',
        ];
    }

    /**
     * Update Recipe.
     *
     * @return void
     */
    protected function v313()
    {
        Schema::table('options', function (Blueprint $table) {
            $table->longText('value')->change();
        });

        $result = DB::select("SHOW COLUMNS FROM options WHERE Field = 'value'");

        if (count($result) && $result[0]->Type == 'longtext') {
            $result[0]->Type;
        } else {
            throw new Exception("We couldn't update spack, Please retry or contact support!", 1);
        }
    }
}
