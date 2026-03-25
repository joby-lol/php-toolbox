<?php

/**
 * Joby's PHP Toolbox: https://go.joby.lol/phptoolbox
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Toolbox\Strings;

class Encoding
{

    public static function base64url_encode(string $input): string
    {
        return rtrim(
            strtr(
                base64_encode($input),
                '+/',
                '-_',
            ),
            '=',
        );
    }

    public static function base64url_decode(string $input): string
    {
        return base64_decode(
            strtr(
                $input,
                '-_',
                '+/',
            ),
        );
    }

}
