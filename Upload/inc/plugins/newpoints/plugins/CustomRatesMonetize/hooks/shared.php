<?php

/***************************************************************************
 *
 *    OUGC Custom Rates Monetize plugin (/inc/plugins/CustomRatesMonetize/hooks/shared.php)
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

namespace Newpoints\CustomRatesMonetize\Hooks\Shared;

use function Newpoints\Core\get_setting;
use function Newpoints\Core\language_load;
use function Newpoints\Core\points_add_simple;
use function Newpoints\Core\points_format;
use function Newpoints\Core\points_substract;
use function Newpoints\Core\rules_forum_get_rate;
use function Newpoints\Core\rules_get_group_rate;
use function ougc\CustomRates\Core\logGet;
use function ougc\CustomRates\Core\rateGet;

function ougc_custom_reputation_log_insert_start(array &$hook_arguments): array
{
    $rate_id = (int)$hook_arguments['insertData']['rid'];

    $rate_data = rateGet($rate_id);

    $log_points = (float)$rate_data['newpoints_price'];

    $post_id = (int)$hook_arguments['insertData']['pid'];

    $post_data = get_post($post_id);

    $log_user_id = (int)$hook_arguments['insertData']['uid'];

    if (!empty($log_points)) {
        global $errorFunction;

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

            if ($log_points > $post_user_data['newpoints'] && !empty($errorFunction)) {
                global $lang;

                language_load('custom_rates_monetize');

                $errorFunction(
                    $lang->sprintf(
                        $lang->newpoints_custom_rates_monetize_error_not_enough_points,
                        points_format($log_points)
                    )
                );
            } else {
                $author_share = (int)$rate_data['newpoints_author_share_percentage'];

                points_substract($log_user_id, $log_points);

                $hook_arguments['insertData']['newpoints_charged'] = $log_points;

                if ($author_share > 0 && $author_share <= 100) {
                    $log_points = round($log_points * $author_share / 100, (int)get_setting('main_decimal'));

                    points_add_simple($post_user_id, $log_points);

                    $hook_arguments['insertData']['newpoints_received'] = $log_points;
                }
            }
        }
    }

    return $hook_arguments;
}

function ougc_custom_reputation_log_delete_end(array &$hook_arguments): array
{
    $log_id = (int)$hook_arguments['logID'];

    $log_data = logGet($log_id);

    $newpoints_received = (float)$log_data['newpoints_received'];

    $points_charged = (float)$log_data['newpoints_charged'];

    $post_id = (int)$log_data['pid'];

    $post_data = get_post($post_id);

    $log_user_id = (int)$log_data['uid'];

    if (!empty($post_data['uid']) && !empty($newpoints_received)) {
        global $errorFunction;

        $post_user_id = (int)$post_data['uid'];

        $post_user_data = get_user($post_user_id);

        // if the author has no enough Newpoints, then stop the rate from being deleted
        // this will only work when the user deleting the rate is the rating user
        // won't work when deleting posts, etc
        if ($newpoints_received > $post_user_data['newpoints'] && !empty($errorFunction)) {
            global $lang;

            language_load('custom_rates_monetize');

            $errorFunction(
                $lang->sprintf(
                    $lang->newpoints_custom_rates_monetize_error_not_enough_points_author,
                    htmlspecialchars_uni($post_user_data['username']),
                    points_format($newpoints_received)
                )
            );
        }

        points_substract($post_user_id, $newpoints_received);

        points_add_simple($log_user_id, $points_charged);
    }

    return $hook_arguments;
}