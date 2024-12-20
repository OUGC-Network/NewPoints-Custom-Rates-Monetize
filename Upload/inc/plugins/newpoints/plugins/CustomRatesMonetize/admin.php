<?php

/***************************************************************************
 *
 *    OUGC Custom Rates Monetize plugin (/inc/plugins/CustomRatesMonetize/admin.php)
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

namespace Newpoints\CustomRatesMonetize\Admin;

use function Newpoints\Admin\db_build_field_definition;
use function Newpoints\Admin\db_drop_columns;
use function Newpoints\Admin\db_verify_columns;
use function Newpoints\Admin\db_verify_columns_exists;
use function Newpoints\Core\language_load;
use function Newpoints\Core\log_remove;
use function Newpoints\Core\plugins_version_delete;
use function Newpoints\Core\plugins_version_get;
use function Newpoints\Core\plugins_version_update;
use function Newpoints\Core\settings_remove;
use function Newpoints\Core\templates_remove;

const FIELDS_DATA = [
    'ougc_customrep' => [
        'newpoints_price' => [
            'type' => 'DECIMAL',
            'size' => '16,2',
            'default' => 0
        ],
        'newpoints_author_share_percentage' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ]
    ],
    'ougc_customrep_log' => [
        'newpoints_received' => [
            'type' => 'DECIMAL',
            'size' => '16,2',
            'default' => 0
        ],
        'newpoints_charged' => [
            'type' => 'DECIMAL',
            'size' => '16,2',
            'default' => 0
        ]
    ],
    'usergroups' => [
        'newpoints_rate_custom_rates_subtraction' => [
            'type' => 'DECIMAL',
            'size' => '16,2',
            'default' => 100,
            'formType' => 'numericField',
            'formOptions' => [
                //'min' => 0,
                'step' => 0.01,
            ]
        ],
    ],
    'forums' => [
        'newpoints_rate_custom_rates' => [
            'type' => 'DECIMAL',
            'size' => '16,2',
            'default' => 1,
            'formType' => 'numericField',
            'formOptions' => [
                //'min' => 0,
                'step' => 0.01,
            ]
        ],
    ],
];

function plugin_information(): array
{
    global $lang;

    language_load('custom_rates_monetize');

    return [
        'name' => 'Custom Rates Monetize',
        'description' => $lang->newpoints_custom_rates_monetize,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '3.1.1',
        'versioncode' => 3101,
        'compatibility' => '31*',
        'codename' => 'newpoints_custom_rates_monetiz'
    ];
}

function plugin_activation(): bool
{
    global $db;

    language_load('custom_rates_monetize');

    $current_version = plugins_version_get('custom_rates_monetize');

    $new_version = (int)plugin_information()['versioncode'];

    /*~*~* RUN UPDATES START *~*~*/

    if ($db->field_exists('points', 'ougc_customrep')) {
        $db->rename_column(
            'ougc_customrep',
            'points',
            'newpoints_price',
            db_build_field_definition(FIELDS_DATA['ougc_customrep']['newpoints_price'])
        );
    }

    if ($db->field_exists('points', 'ougc_customrep_log')) {
        $db->rename_column(
            'ougc_customrep_log',
            'points',
            'newpoints_received',
            db_build_field_definition(FIELDS_DATA['ougc_customrep_log']['newpoints_received'])
        );
    }

    /*~*~* RUN UPDATES END *~*~*/

    db_verify_columns(FIELDS_DATA);

    plugins_version_update('custom_rates_monetize', $new_version);

    return true;
}

function plugin_deactivation(): bool
{
    return true;
}

function plugin_is_installed(): bool
{
    return db_verify_columns_exists(FIELDS_DATA);
}

function plugin_uninstallation(): bool
{
    log_remove(
        [
            'custom_rates_monetize_charge',
            'custom_rates_monetize_author_share',
            'custom_rates_monetize_delete_charge',
            'custom_rates_monetize_delete_author_share'
        ]
    );

    db_drop_columns(FIELDS_DATA);

    settings_remove(
        [
            'action_name'
        ],
        'newpoints_custom_rates_monetize_'
    );

    templates_remove([''], 'newpoints_custom_rates_monetize_');

    plugins_version_delete('custom_rates_monetize');

    return true;
}