<?php

declare(strict_types=1);

namespace App\Service;

class Slug
{

    public function slugify(string $text, string $divider = '_'): string
    {
        // replace non letter or digits by divider
        /**
 * @var string $text 
*/
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // encoding
        /**
 * @var string $text 
*/
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove special characters
        /**
 * @var string $text 
*/
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        /**
 * @var string $text 
*/
        $text = trim($text, $divider);

        // remove duplicate divider
        /**
 * @var string $text 
*/
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        /**
 * @var string $text 
*/
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

}
