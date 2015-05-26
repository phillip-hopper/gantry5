<?php
/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Gantry\Admin;

use Gantry\Component\Request\Request;
use Gantry\Component\Response\JsonResponse;
use Gantry\Component\Response\Response;
use Gantry\Component\Router\Router as BaseRouter;

/**
 * Gantry administration router for WordPress.
 */
class Router extends BaseRouter
{
    public function boot()
    {
        /** @var Request $request */
        $request = $this->container['request'];

        $this->method = $request->getMethod();
        $this->path = explode('/', isset( $_GET['view'] ) ? $_GET['view'] : 'about');
        $this->resource = array_shift($this->path) ?: 'themes';

        // FIXME: make it better by detecting admin-ajax.php..
        $ajax = isset( $_GET['gantry5'] );
        $this->format = $ajax ? 'json' : 'html';

        $this->params = [
            'ajax' => $ajax,
            'location' => $this->resource,
            'method' => $this->method,
            'format' => $this->format,
            'params' => isset($_POST['params']) && is_string($_POST['params']) ? json_decode($_POST['params'], true) : []
        ];

        $this->setTemplate();

        return $this;
    }

    protected function makeUri($url)
    {
        $components = parse_url($url);

        $path     = isset($components['path']) ? $components['path'] : '';
        $query    = isset($components['query']) ? '?' . $components['query'] : '';
        $fragment = isset($components['fragment']) ? '#' . $components['fragment'] : '';

        return "{$path}{$query}{$fragment}";
    }

    public function setTemplate()
    {
        $this->container['base_url'] = $this->makeUri(\admin_url( 'admin-ajax.php?page=layout-manager' ));

        $this->container['ajax_suffix'] = '&action=gantry5';

        // Create nonce
        $nonce = wp_create_nonce( 'gantry5-layout-manager' );

        $this->container['routes'] = [
            '1' => "&view=%s&_wpnonce={$nonce}",

            'themes' => '&view=themes',
            'picker/layouts' => "&view=layouts&_wpnonce={$nonce}",
        ];

        return $this;
    }

    protected function checkSecurityToken()
    {
        // Check security nonce and return false on failure.
        if(check_admin_referer('gantry5-layout-manager')) {
            return true;
        }

        return false;
    }

    /**
     * Send response to the client.
     *
     * @param Response $response
     */
    protected function send(Response $response)
    {
        // Output HTTP header.
        header("HTTP/1.1 {$response->getStatus()}", true, $response->getStatusCode());
        header("Content-Type: {$response->mimeType}; charset={$response->charset}");
        foreach ($response->getHeaders() as $key => $values) {
            $replace = true;
            foreach ($values as $value) {
                header("{$key}: {$value}", $replace);
                $replace = false;
            }
        }

        if ($response instanceof JsonResponse) {
            // Output Gantry response.
            echo $response;
            die();
        }

        return $response;
    }
}
