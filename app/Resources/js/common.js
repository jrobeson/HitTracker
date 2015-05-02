/**
 * This file is part of HitTracker.
 *
 * HitTracker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2014 <johnny@localmomentum.net>
 * @license AGPL-3
 */
if (!window.location.origin) {
    let schemeHost = `${window.location.protocol}//${window.location.hostname}`;
    let port = (window.location.port) ? `:${window.location.port}` : '';
    window.location.origin = `${schemeHost}${port}`;
}

alertDismiss = function () {
    let target = $('.alert');
    let timeout = target.data('auto-dismiss');

    if (!timeout) {
        return;
    }
    timeout = parseInt(timeout) * 1000;
    setTimeout(function() {
        target.fadeTo(500, 0).slideUp(500, function() { $(this).remove() })
    }, timeout);
};
$(document).ready(()=> alertDismiss());
