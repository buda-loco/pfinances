<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            'Canberra 🇦🇺',
            'Brisbane 🇦🇺',
            'Japon 🇯🇵',
            'England 🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'Korea 🇰🇷',
            'Vietnam 🇻🇳',
            'China 🇨🇳',
            'Spain 🇪🇸',
            'Italy 🇮🇹',
            'Poland 🇵🇱',
            'Dominicana 🇩🇴',
            'Colombia 🇨🇴',
            'Tucuman 🇦🇷',
            'Brasil 🇧🇷',
            'Edinburgo 🏴󠁧󠁢󠁳󠁣󠁴󠁿',
            'USA 🇺🇸',
            'Canada 🇨🇦',
            'Hong Kong 🇭🇰',
            'Fiji 🇫🇯',
            'France 🇫🇷',
            'Online stuff 🖥️',
            'Viaje gral ✈️',
            'crucero 🛳️',
            'Work stuff 💪',
            'Europa 🇪🇺', // Normalized from 🇪🇺 Europa
            'UK 🇬🇧',     // Normalized from 🇬🇧 UK
            'Australia 🇦🇺', // Normalized from 🇦🇺Australia
        ];

        foreach ($projects as $projectName) {
            // Generate a code from the name (removing emojis and spaces)
            // Note: simple regex to keep only letters/numbers
            $cleanName = preg_replace('/[^\p{L}\p{N}\s]/u', '', $projectName);
            $code = Str::upper(Str::slug($cleanName, ''));

            // Fallback if code is empty (e.g. only emoji name? unlikely here)
            if (empty($code)) {
                $code = 'PROJ_' . Str::random(5);
            }

            Project::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $projectName,
                    'status' => 'active',
                    'color' => $this->getColorForProject($code),
                ]
            );
        }
    }

    private function getColorForProject($code)
    {
        $colors = [
            '#EF4444', // Red
            '#F97316', // Orange
            '#F59E0B', // Amber
            '#84CC16', // Lime
            '#10B981', // Emerald
            '#06B6D4', // Cyan
            '#3B82F6', // Blue
            '#6366F1', // Indigo
            '#8B5CF6', // Violet
            '#D946EF', // Fuchsia
            '#F43F5E', // Rose
        ];

        // Deterministic color assignment based on code string
        $hash = crc32($code);
        return $colors[$hash % count($colors)];
    }
}
