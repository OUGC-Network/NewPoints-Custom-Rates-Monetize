<?php

/***************************************************************************
 *
 *    NewPoints Custom Rates Monetize plugin (/inc/plugins/CustomRatesMonetize/core.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2024 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Require users to spend points to rate posts using the Custom Rates plugin.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace Newpoints\CustomRatesMonetize\Core;

use function Newpoints\Core\language_load;

use const Newpoints\CustomRatesMonetize\ROOT;

function templates_get(string $template_name = '', bool $enable_html_comments = true): string
{
    return \Newpoints\Core\templates_get($template_name, $enable_html_comments, ROOT, 'custom_rates_monetize_');
}

function customRatesPluginIsInstalled(): bool
{
    global $cache;

    $plugins_list = $cache->read('ougc_plugins');

    return !empty($plugins_list['customrep']) && defined('ougc\CustomRates\ROOT');
}