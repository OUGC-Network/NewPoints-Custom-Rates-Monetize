<?php

/***************************************************************************
 *
 *    NewPoints Custom Rates Monetize plugin (/inc/plugins/CustomRatesMonetize/hooks/forum.php)
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

namespace Newpoints\CustomRatesMonetize\Hooks\Forum;

use function Newpoints\Core\get_setting;
use function Newpoints\Core\language_load;
use function Newpoints\Core\points_format;
use function Newpoints\Core\post_parser;
use function Newpoints\Core\rules_forum_get_rate;
use function Newpoints\Core\rules_get_group_rate;
use function Newpoints\CustomRatesMonetize\Core\customRatesPluginIsInstalled;
use function Newpoints\CustomRatesMonetize\Core\templates_get;
use function ougc\CustomRates\Core\rateGet;
use function ougc\CustomRates\Core\rateGetName;

function newpoints_global_start(array &$hook_arguments): array
{
    $hook_arguments['newpoints.php'] = array_merge($hook_arguments['newpoints.php'], [
        'newpoints_custom_rates_monetize_page_table_transactions_row',
        'newpoints_custom_rates_monetize_page_table_transactions',
    ]);

    return $hook_arguments;
}

function ougc_custom_rates_reputation_add_process_start(array &$hook_arguments): array
{
    global $mybb;
    global $reputation;

    $log_points = (float)$hook_arguments['rateData']['newpoints_price'];

    $post_id = (int)$reputation['pid'];

    $post_data = get_post($post_id);

    $log_user_id = (int)$mybb->user['uid'];

    if (!empty($log_points)) {
        $post_user_id = (int)$post_data['uid'];

        $post_user_data = get_user($post_user_id);

        $forum_rate = rules_forum_get_rate((int)$post_data['fid']);

        if (!empty($forum_rate['rate'])) {
            $forum_rate = (int)$forum_rate['rate'];
        } else {
            $forum_rate = 1;
        }

        $group_rate = rules_get_group_rate(get_user($log_user_id));

        if (!empty($group_rate['rate'])) {
            $group_rate = (int)$group_rate['rate'];
        } else {
            $group_rate = 1;
        }

        if ($forum_rate && $group_rate) {
            $log_points = round(
                $log_points * $forum_rate * $group_rate,
                (int)get_setting('main_decimal')
            );

            if ($log_points > $post_user_data['newpoints']) {
                global $lang, $templates, $theme;

                language_load('custom_rates_monetize');

                $message = $lang->sprintf(
                    $lang->newpoints_custom_rates_monetize_error_not_enough_points,
                    points_format($log_points)
                );

                if (!empty($mybb->input['nomodal'])) {
                    echo eval($templates->render('reputation_add_error_nomodal', false));
                } else {
                    echo eval($templates->render('reputation_add_error', false));
                }

                exit;
            }
        }
    }

    //$hook_arguments['rateData']['createCoreReputationType'] = 0;

    return $hook_arguments;
}

function newpoints_home_end(): bool
{
    if (!customRatesPluginIsInstalled()) {
        return false;
    }

    if (!($limit = (int)get_setting('custom_rates_monetize_home_transactions'))) {
        return false;
    }

    global $mybb, $db, $lang, $theme;
    global $latest_transactions;

    language_load('custom_rates_monetize');

    $current_user_id = (int)$mybb->user['uid'];

    $where_clauses = [
        "(l.uid='{$current_user_id}' AND l.newpoints_charged > 0) OR (p.uid='{$current_user_id}' AND l.newpoints_received > 0)"
    ];

    if ($inactive_forums = get_unviewable_forums(true)) {
        $where_clauses[] = "p.fid NOT IN ({$inactive_forums})";
    }

    if ($inactive_forums = get_inactive_forums()) {
        $where_clauses[] = "p.fid NOT IN ({$inactive_forums})";
    }

    $query = $db->simple_select(
        "ougc_customrep_log l LEFT JOIN {$db->table_prefix}posts p ON (p.pid=l.pid)",
        'l.lid, l.uid, l.pid, l.dateline, l.newpoints_received, l.newpoints_charged, p.subject, p.tid, p.uid AS post_uid',
        implode(' AND ', $where_clauses),
        ['order_by' => 'l.dateline', 'order_dir' => 'DESC', 'limit' => $limit]
    );

    if (!$db->num_rows($query)) {
        return false;
    }

    global $mybb;

    $trow = alt_trow(true);

    $logs_list = '';

    while ($log_data = $db->fetch_array($query)) {
        $post_subject = post_parser()->parse_badwords($log_data['subject']);

        $post_id = (int)$log_data['pid'];

        $post_url = get_post_link($post_id, (int)$log_data['tid']);

        $points_received = $points_charged = $from_user_name = $to_user_name = '-';

        if ((int)$log_data['uid'] !== $current_user_id) {
            $points_received = points_format((float)$log_data['newpoints_received']);
        } else {
            $points_charged = points_format((float)$log_data['newpoints_charged']);
        }

        $from_user_data = get_user((int)$log_data['uid']);

        $from_user_name = build_profile_link(
            format_name(
                htmlspecialchars_uni($from_user_data['username']),
                $from_user_data['usergroup'],
                $from_user_data['displaygroup']
            ),
            $log_data['uid']
        );

        $to_user_data = get_user((int)$log_data['post_uid']);

        $to_user_name = build_profile_link(
            format_name(
                htmlspecialchars_uni($to_user_data['username']),
                $to_user_data['usergroup'],
                $to_user_data['displaygroup']
            ),
            $log_data['uid']
        );

        $log_stamp = my_date('normal', $log_data['dateline']);

        $logs_list .= eval(templates_get('page_table_transactions_row'));

        $trow = alt_trow();
    }

    $latest_transactions[] = eval(templates_get('page_table_transactions'));

    return true;
}

function newpoints_logs_log_row(): bool
{
    if (!customRatesPluginIsInstalled()) {
        return false;
    }

    global $log_data;

    if (!in_array($log_data['action'], [
        'custom_rates_monetize_charge',
        'custom_rates_monetize_author_share',
        'custom_rates_monetize_delete_author_share',
        'custom_rates_monetize_delete_charge'
    ])) {
        return false;
    }

    global $lang;
    global $log_action, $log_primary, $log_secondary;

    language_load('custom_rates_monetize');

    $rate_id = (int)$log_data['log_primary_id'];

    $rate_data = rateGet($rate_id);

    if (!empty($rate_data)) {
        if (!$rate_name = rateGetName($rate_id)) {
            $rate_name = htmlspecialchars_uni($rate_data['name']);
        }

        $log_primary = $lang->sprintf($lang->newpoints_custom_rates_monetize_page_logs_primary, $rate_name);
    }

    $post_id = (int)$log_data['log_secondary_id'];

    $post_data = get_post($post_id);

    if (!empty($post_data)) {
        global $mybb;

        $post_url = get_post_link($post_id, (int)$post_data['tid']);

        $post_subject = post_parser()->parse_badwords($post_data['subject']);

        $log_secondary = $lang->sprintf(
            $lang->newpoints_custom_rates_monetize_page_logs_secondary,
            $mybb->settings['bburl'],
            $post_url,
            $post_id,
            $post_subject
        );
    }

    if ($log_data['action'] === 'custom_rates_monetize_charge') {
        $log_action = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_charge;
    } elseif ($log_data['action'] === 'custom_rates_monetize_author_share') {
        $log_action = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_author_share;
    } elseif ($log_data['action'] === 'custom_rates_monetize_delete_charge') {
        $log_action = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_author_share;
    } elseif ($log_data['action'] === 'custom_rates_monetize_delete_author_share') {
        $log_action = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_delete_author_share;
    }

    return true;
}

function newpoints_logs_end(): bool
{
    global $lang;
    global $action_types;

    language_load('custom_rates_monetize');

    foreach ($action_types as $key => &$action_type) {
        if ($key === 'custom_rates_monetize_charge') {
            $action_type = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_charge;
        } elseif ($key === 'custom_rates_monetize_author_share') {
            $action_type = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_author_share;
        } elseif ($key === 'custom_rates_monetize_delete_charge') {
            $action_type = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_author_share;
        } elseif ($key === 'custom_rates_monetize_delete_author_share') {
            $action_type = $lang->newpoints_custom_rates_monetize_page_logs_custom_rates_monetize_delete_author_share;
        }
    }

    return true;
}