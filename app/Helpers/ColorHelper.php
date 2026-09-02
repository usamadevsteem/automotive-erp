<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Map common Pakistani car color names to their hex equivalents.
     * Used to render small color swatches in the inventory table.
     */
    private static array $map = [
        // Whites
        'white'        => '#FFFFFF',
        'pearl white'  => '#F5F5F0',
        'off white'    => '#FAF9F6',
        'super white'  => '#FFFFFF',

        // Blacks
        'black'        => '#1C1C1C',
        'midnight black'=> '#1C1C1C',
        'phantom black' => '#1C1C1C',

        // Greys / Silvers
        'silver'       => '#C0C0C0',
        'grey'         => '#808080',
        'gray'         => '#808080',
        'dark grey'    => '#404040',
        'graphite'     => '#555555',
        'platinum'     => '#D4D4D4',
        'lunar silver' => '#BEC1C3',

        // Reds
        'red'          => '#D0021B',
        'maroon'       => '#800000',
        'wine'         => '#722F37',
        'carmine red'  => '#960018',
        'dark red'     => '#8B0000',
        'red mica'     => '#C0392B',

        // Blues
        'blue'         => '#1A6BB5',
        'dark blue'    => '#003580',
        'navy blue'    => '#001F5B',
        'light blue'   => '#5DADE2',
        'cobalt blue'  => '#0047AB',
        'sky blue'     => '#87CEEB',
        'ocean blue'   => '#006994',

        // Greens
        'green'        => '#2ECC71',
        'dark green'   => '#1A5276',
        'olive green'  => '#808000',
        'military green'=> '#4B5320',
        'emerald'      => '#50C878',

        // Browns / Beiges
        'brown'        => '#8B4513',
        'beige'        => '#F5F5DC',
        'champagne'    => '#F7E7CE',
        'golden'       => '#FFD700',
        'bronze'       => '#CD7F32',
        'copper'       => '#B87333',

        // Others
        'orange'       => '#FFA500',
        'yellow'       => '#FFD700',
        'purple'       => '#6C3483',
        'brown grey'   => '#9E8E7E',
    ];

    public static function toHex(?string $colorName): string
    {
        if (!$colorName) return '#E0E0E0';

        $key = strtolower(trim($colorName));

        return self::$map[$key] ?? '#E0E0E0';
    }
}
