<?php

/***************************************************************************
 *
 *    NewPoints Custom Rates Monetize plugin (/inc/plugins/CustomRatesMonetize/admin.php)
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
use function Newpoints\CustomRatesMonetize\Core\custom_rates_plugin_is_installed;

use const Newpoints\Core\FORM_TYPE_NUMERIC_FIELD;
use const Newpoints\DECIMAL_DATA_TYPE_SIZE;
use const Newpoints\DECIMAL_DATA_TYPE_STEP;

const FIELDS_DATA = [
    'ougc_customrep' => [
        'newpoints_price' => [
            'type' => 'DECIMAL',
            'unsigned' => true,
            'size' => DECIMAL_DATA_TYPE_SIZE,
            'default' => 0,
            'form_type' => FORM_TYPE_NUMERIC_FIELD,
            'form_options' => [
                'step' => DECIMAL_DATA_TYPE_STEP,
            ]
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
            'unsigned' => true,
            'size' => DECIMAL_DATA_TYPE_SIZE,
            'default' => 0
        ],
        'newpoints_charged' => [
            'type' => 'DECIMAL',
            'unsigned' => true,
            'size' => DECIMAL_DATA_TYPE_SIZE,
            'default' => 0
        ]
    ],
    'usergroups' => [
        'newpoints_rate_custom_rates_subtraction' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 100,
            'form_type' => FORM_TYPE_NUMERIC_FIELD,
            'form_options' => [
                //'min' => 0,
                'step' => 0.01,
            ]
        ],
    ],
    'forums' => [
        'newpoints_rate_custom_rates' => [
            'type' => 'DECIMAL',
            'unsigned' => true,
            'size' => DECIMAL_DATA_TYPE_SIZE,
            'default' => 1,
            'form_type' => FORM_TYPE_NUMERIC_FIELD,
            'form_options' => [
                //'min' => 0,
                'step' => DECIMAL_DATA_TYPE_STEP,
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
        'description' => $lang->newpoints_custom_rates_monetize_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '3.1.3',
        'versioncode' => 3103,
        'compatibility' => '31*',
        'codename' => 'newpoints_custom_rates_monetiz'
    ];
}

function plugin_activation(): bool
{
    if (!custom_rates_plugin_is_installed()) {
        global $lang;

        language_load('custom_rates_monetize');

        flash_message($lang->newpoints_custom_rates_monetize_plugin_missing, 'error');

        admin_redirect('index.php?module=newpoints-plugins');

        return false;
    }

    global $db;

    $current_version = plugins_version_get('custom_rates_monetize');

    $new_version = (int)plugin_information()['versioncode'];

    /*~*~* RUN UPDATES START *~*~*/

    if ($db->table_exists('ougc_customrep') && $db->field_exists('points', 'ougc_customrep')) {
        $db->rename_column(
            'ougc_customrep',
            'points',
            'newpoints_price',
            db_build_field_definition(FIELDS_DATA['ougc_customrep']['newpoints_price'])
        );
    }

    if ($db->table_exists('ougc_customrep_log') && $db->field_exists('points', 'ougc_customrep_log')) {
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

    templates_remove(['page_table_transactions', 'page_table_transactions_row'], 'newpoints_custom_rates_monetize_');

    plugins_version_delete('custom_rates_monetize');

    return true;
}