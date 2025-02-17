<?php
namespace Opencart\Catalog\Controller\Extension\Currency\Event;

use Opencart\System\Engine\Action;

/**
 * Class Currency
 *
 * @package Opencart\Catalog\Controller\Extension\Currency\Event
 */
class Currency extends \Opencart\System\Engine\Controller
{

    /**
     * @param string $route
     * @param array $args
     * @return Action
     */
    public function format(string &$route, array &$args)
    {
        var_dump($args);
        exit;
    }
}