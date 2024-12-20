<?php

/***************************************************************************
 *
 *    OUGC Custom Rates Monetize plugin (/inc/plugins/newpoints_custom_rates_monetize.php)
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

use function Newpoints\CustomRatesMonetize\Admin\plugin_information;
use function Newpoints\CustomRatesMonetize\Admin\plugin_activation;
use function Newpoints\CustomRatesMonetize\Admin\plugin_deactivation;
use function Newpoints\CustomRatesMonetize\Admin\plugin_is_installed;
use function Newpoints\CustomRatesMonetize\Admin\plugin_uninstallation;
use function Newpoints\Core\add_hooks;

use const Newpoints\CustomRatesMonetize\ROOT;
use const Newpoints\ROOT_PLUGINS;

defined('IN_MYBB') || die('Direct initialization of this file is not allowed.');

define('Newpoints\CustomRatesMonetize\ROOT', ROOT_PLUGINS . '/CustomRatesMonetize');

require_once ROOT . '/core.php';

if (defined('IN_ADMINCP')) {
    require_once ROOT . '/admin.php';
    require_once ROOT . '/hooks/admin.php';

    add_hooks('Newpoints\CustomRatesMonetize\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    add_hooks('Newpoints\CustomRatesMonetize\Hooks\Forum');
}

require_once ROOT . '/hooks/shared.php';

add_hooks('Newpoints\CustomRatesMonetize\Hooks\Shared');

function newpoints_custom_rates_monetize_info(): array
{
    return plugin_information();
}

function newpoints_custom_rates_monetize_activate(): bool
{
    return plugin_activation();
}

function newpoints_custom_rates_monetize_deactivate(): bool
{
    return plugin_deactivation();
}

function newpoints_custom_rates_monetize_uninstall(): bool
{
    return plugin_uninstallation();
}

function newpoints_custom_rates_monetize_is_installed(): bool
{
    return plugin_is_installed();
}

(function () {
    global $groupzerolesser, $grouppermbyswitch;

    $groupzerolesser[] = 'newpoints_rate_custom_rates_subtraction';

    $grouppermbyswitch['newpoints_rate_custom_rates_subtraction'] = 'newpoints_can_get_points';
})();