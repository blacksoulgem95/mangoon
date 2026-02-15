<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
                'native_name' => 'Italiano',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'ja',
                'name' => 'Japanese',
                'native_name' => '日本語',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'pt',
                'name' => 'Portuguese',
                'native_name' => 'Português',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'pt-BR',
                'name' => 'Portuguese (Brazil)',
                'native_name' => 'Português (Brasil)',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'zh',
                'name' => 'Chinese (Simplified)',
                'native_name' => '简体中文',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'zh-TW',
                'name' => 'Chinese (Traditional)',
                'native_name' => '繁體中文',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'ko',
                'name' => 'Korean',
                'native_name' => '한국어',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
                'native_name' => 'Русский',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'th',
                'name' => 'Thai',
                'native_name' => 'ไทย',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'vi',
                'name' => 'Vietnamese',
                'native_name' => 'Tiếng Việt',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'id',
                'name' => 'Indonesian',
                'native_name' => 'Bahasa Indonesia',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
                'native_name' => 'Türkçe',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'pl',
                'name' => 'Polish',
                'native_name' => 'Polski',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'nl',
                'name' => 'Dutch',
                'native_name' => 'Nederlands',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'sv',
                'name' => 'Swedish',
                'native_name' => 'Svenska',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Languages seeded successfully!');
    }
}
