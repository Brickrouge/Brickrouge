<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Brickrouge;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use function is_string;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;

final class CasesTest extends TestCase
{
    /**
     * @dataProvider provide_test_case
     */
    public function test_case(string $case): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . "cases" . DIRECTORY_SEPARATOR . "$case";

        ob_start();
        include "$path.php";
        $html = ob_get_clean();

        assert(is_string($html));
        $this->assertStringEqualsFile("$path.html", $html);
    }

    /**
     * @return array<array{ string }>
     */
    public static function provide_test_case(): array
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'cases' . DIRECTORY_SEPARATOR;
        $dir_prefix_len = strlen($dir);
        $di = new RecursiveDirectoryIterator($dir);
        $it = new RecursiveIteratorIterator($di);
        $iterator = new RegexIterator($it, '/\.php$/');
        $cases = [];

        foreach ($iterator as $file) {
            $cases[] = [ substr($file->getPathname(), $dir_prefix_len, -4) ];
        }

        sort($cases);

        return $cases;
    }
}
