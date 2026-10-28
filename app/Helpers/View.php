<?php
/**
 * View renderer — outputs a view file wrapped in a layout.
 */

declare(strict_types=1);

namespace App\Helpers;

class View
{
    /**
     * Render a view file, optionally wrapping it in a layout.
     *
     * @param string $view   Dot- or slash-delimited view path relative to resources/views/
     *                       e.g. 'admin.dashboard' or 'admin/dashboard'
     * @param array  $data   Variables to extract into the view scope
     * @param string $layout Layout file name in resources/views/layouts/ (empty = no layout)
     */
    public static function render(string $view, array $data = [], string $layout = 'base'): void
    {
        // Normalise dot notation to directory separator
        $viewPath = BASE_PATH . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        // Extract variables into the view scope
        extract($data, EXTR_SKIP);

        // Capture view output
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Wrap in layout
        if ($layout !== '') {
            $layoutPath = BASE_PATH . '/resources/views/layouts/' . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout not found: {$layoutPath}");
            }
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Safely escape a value for HTML output.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

