<?php

/***************************************************************************
 *
 *    OUGC Custom Rates Monetize plugin (/inc/plugins/newpoints/languages/english/admin/newpoints_custom_rates_monetize.lang.php)
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

$l = [
    'newpoints_custom_rates_monetize' => 'Custom Rates Monetize',
    'newpoints_custom_rates_monetize_desc' => 'Require users to spend points to rate posts using the Custom Rates plugin.',

    'newpoints_custom_rates_monetize_plugin_missing' => 'This plugin requires the <a href="https://community.mybb.com/mods.php?action=view&pid=234">Custom Rates</a> plugin to be installed and activated.',

    'setting_group_newpoints_custom_rates_monetize' => 'Custom Rates Monetize',
    'setting_group_newpoints_custom_rates_monetize_desc' => 'Require users to spend points to rate posts using the Custom Rates plugin.',

    'setting_newpoints_custom_rates_monetize_home_transactions' => 'Home Transactions',
    'setting_newpoints_custom_rates_monetize_home_transactions_desc' => 'Set how many last transactions to show in the Newpoints home page.',

    'newpoints_custom_rates_monetize_row_price' => 'Newpoints Price',
    'newpoints_custom_rates_monetize_row_price_desc' => 'Set a cost for users to rate posts using this custom rate.',
    'newpoints_custom_rates_monetize_row_author_share_percentage' => 'Newpoints Author Share Percentage',
    'newpoints_custom_rates_monetize_row_author_share_percentage_desc' => 'Percentage of the cost that the author of the post will receive. Points are returned to the rating user if the rate is undone.',

    'newpoints_user_groups_rate_custom_rates_subtraction' => 'Custom Rates Rate <code style="color: darkorange;">This works as a percentage. So "0" = user does not pay anything "100" = users pay full price, "200" = user pays twice the price, etc.</code><br /><small class="input">The custom rates rate for this group, used when subtracting points from users when they rate a post (multiplies the <code>Newpoints Price</code> custom rate setting). Default is <code>100</code>.</small><br />',

    'newpoints_forums_rate_custom_rates' => 'Custom Rates Rate<br /><small class="input">The rate for rating posts in this forum. Default is <code>1</code>.</small><br />'
];