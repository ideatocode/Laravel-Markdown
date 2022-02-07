<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Markdown.
 *
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Markdown;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Blade;
use League\CommonMark\MarkdownConverter as CommonMarkMarkdownConverter;

class MarkdownConverter extends CommonMarkMarkdownConverter
{

    public function parseDataToHtml($string, $data)
    {

        $text = preg_replace_callback('/{{\s*(.+?)\s*}}/', function ($matches) use ($data) {
            return data_get($data, $matches[1]);
        }, $string);

        return $this->convertToHtml($text);
    }
    public function parseBladeToHtml($string, $data)
    {
        $php = Blade::compileString("@markdown\n" . ($string ?? '') . "\n@endmarkdown");
        $rendered = $this->render($php, $data);
        return $rendered;
    }

    private function render($__php, $__data)
    {
        $obLevel = ob_get_level();
        ob_start();
        extract($__data, EXTR_SKIP);
        try {
            eval('?' . '>' . $__php);
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) ob_end_clean();
            // throw $e;
            return $e->getMessage();
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) ob_end_clean();
            // throw new Exception($e);
            return $e->getMessage();
        }
        return ob_get_clean();
    }
}
