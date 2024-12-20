<?php

/***************************************************************************
 *
 *    OUGC Custom Rates Monetize plugin (/inc/plugins/CustomRatesMonetize/hooks/admin.php)
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

namespace Newpoints\CustomRatesMonetize\Hooks\Admin;

use function Newpoints\Core\language_load;

use const Newpoints\CustomRatesMonetize\Admin\FIELDS_DATA;
use const Newpoints\CustomRatesMonetize\ROOT;

function newpoints_settings_rebuild_start(array &$hook_arguments): array
{
    language_load('custom_rates_monetize');

    $hook_arguments['settings_directories'][] = ROOT . '/settings';

    return $hook_arguments;
}

function newpoints_templates_rebuild_start(array $hook_arguments): array
{
    $hook_arguments['templates_directories']['custom_rates_monetize'] = ROOT . '/templates';

    return $hook_arguments;
}

function newpoints_admin_settings_intermediate(array &$hook_arguments): array
{
    language_load('custom_rates_monetize');

    //unset($hook_arguments['active_plugins']['newpoints_custom_rates_monetize']);

    $hook_arguments['custom_rates_monetize'] = [];

    return $hook_arguments;
}

function newpoints_admin_settings_commit_start(array &$setting_groups_objects): array
{
    return newpoints_admin_settings_intermediate($setting_groups_objects);
}

function newpoints_admin_user_groups_edit_graph_start(array &$hook_arguments): array
{
    language_load('custom_rates_monetize');

    $hook_arguments['data_fields'] = array_merge(
        $hook_arguments['data_fields'],
        FIELDS_DATA['usergroups']
    );

    return $hook_arguments;
}

function newpoints_admin_user_groups_edit_commit_start(array &$hook_arguments): array
{
    return newpoints_admin_user_groups_edit_graph_start($hook_arguments);
}

function newpoints_admin_formcontainer_end_start(array &$hook_arguments): array
{
    language_load('custom_rates_monetize');

    $hook_arguments['data_fields'] = array_merge(
        $hook_arguments['data_fields'],
        FIELDS_DATA['forums']
    );

    return $hook_arguments;
}

function newpoints_admin_forum_management_edit_commit_start(array &$hook_arguments): array
{
    return newpoints_admin_formcontainer_end_start($hook_arguments);
}

function ougc_custom_rates_cache_update_start(array &$hook_arguments): array
{
    $hook_arguments['dbFields'][] = 'newpoints_price';

    return $hook_arguments;
}

function ougc_custom_rates_cache_update_intermediate(array &$hook_arguments): array
{
    $hook_arguments['cacheData'][(int)$hook_arguments['rateData']['rid']]['newpoints_price'] = (float)$hook_arguments['rateData']['newpoints_price'];

    return $hook_arguments;
}

function ougc_custom_rates_admin_add_start(): bool
{
    global $rateInputData;

    $rateInputData['newpoints_price'] = 0;

    $rateInputData['newpoints_author_share_percentage'] = 0;

    return true;
}

function ougc_custom_rates_admin_edit_start(): bool
{
    global $rateInputData;

    $rateInputData['newpoints_price'] = (float)$rateInputData['newpoints_price'];

    $rateInputData['newpoints_author_share_percentage'] = (int)$rateInputData['newpoints_author_share_percentage'];

    return true;
}

function ougc_custom_rates_admin_add_end(): bool
{
    global $lang;
    global $form_container, $form;
    global $rateInputData;

    language_load('custom_rates_monetize');

    $form_container->output_row(
        $lang->newpoints_custom_rates_monetize_row_price,
        $lang->newpoints_custom_rates_monetize_row_price_desc,
        $form->generate_numeric_field(
            'newpoints_price',
            $rateInputData['newpoints_price'],
            ['id' => 'newpoints_price', 'min' => 0, 'step' => 0.01]
        )
    );

    $form_container->output_row(
        $lang->newpoints_custom_rates_monetize_row_author_share_percentage,
        $lang->newpoints_custom_rates_monetize_row_author_share_percentage_desc,
        $form->generate_numeric_field(
            'newpoints_author_share_percentage',
            $rateInputData['newpoints_author_share_percentage'],
            ['id' => 'newpoints_author_share_percentage', 'min' => 0, 'max' => 100]
        )
    );

    return true;
}

function ougc_custom_rates_admin_edit_end(): bool
{
    return ougc_custom_rates_admin_add_end();
}

function ougc_custom_reputation_rate_insert_start(array &$hook_arguments): array
{
    if (isset($hook_arguments['rateData'])) {
        $hook_arguments['insertData']['newpoints_price'] = (float)$hook_arguments['rateData']['newpoints_price'];
    }

    if (isset($hook_arguments['rateData'])) {
        $hook_arguments['insertData']['newpoints_author_share_percentage'] = (float)$hook_arguments['rateData']['newpoints_author_share_percentage'];
    }

    return $hook_arguments;
}

function ougc_custom_reputation_rate_update_start(array &$hook_arguments): array
{
    return ougc_custom_reputation_rate_insert_start($hook_arguments);
}