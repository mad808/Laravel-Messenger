<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            [
                'key' => 'site_name',
                'value' => 'Family Messenger',
                'en_name' => 'Family Messenger',
                'ru_name' => 'Семейный Мессенджер',
                'tk_name' => 'Maşgala Messenger',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'en_name' => null,
                'ru_name' => null,
                'tk_name' => null,
                'type' => 'image',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'en_name' => null,
                'ru_name' => null,
                'tk_name' => null,
                'type' => 'image',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_phone',
                'value' => '+99362240774',
                'en_name' => null,
                'ru_name' => null,
                'tk_name' => null,
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_address',
                'value' => 'Ashgabat, Turkmenistan',
                'en_name' => 'Ashgabat, Turkmenistan',
                'ru_name' => 'Ашхабад, Туркменистан',
                'tk_name' => 'Aşgabat, Türkmenistan',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}