<?php

declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Security;

enum CorePermission: string
{
    case Assets                = 'opendxp:security:permission:assets';
    case Classes               = 'opendxp:security:permission:classes';
    case Selectoptions         = 'opendxp:security:permission:selectoptions';
    case ClearCache            = 'opendxp:security:permission:clear_cache';
    case ClearFullpageCache    = 'opendxp:security:permission:clear_fullpage_cache';
    case ClearTempFiles        = 'opendxp:security:permission:clear_temp_files';
    case Dashboards            = 'opendxp:security:permission:dashboards';
    case DocumentTypes         = 'opendxp:security:permission:document_types';
    case Documents             = 'opendxp:security:permission:documents';
    case Emails                = 'opendxp:security:permission:emails';
    case NotesEvents           = 'opendxp:security:permission:notes_events';
    case Objects               = 'opendxp:security:permission:objects';
    case PredefinedProperties  = 'opendxp:security:permission:predefined_properties';
    case AssetMetadata         = 'opendxp:security:permission:asset_metadata';
    case Recyclebin            = 'opendxp:security:permission:recyclebin';
    case Redirects             = 'opendxp:security:permission:redirects';
    case Seemode               = 'opendxp:security:permission:seemode';
    case ShareConfigurations   = 'opendxp:security:permission:share_configurations';
    case SystemSettings        = 'opendxp:security:permission:system_settings';
    case TagsConfiguration     = 'opendxp:security:permission:tags_configuration';
    case TagsAssignment        = 'opendxp:security:permission:tags_assignment';
    case TagsSearch            = 'opendxp:security:permission:tags_search';
    case Thumbnails            = 'opendxp:security:permission:thumbnails';
    case Translations          = 'opendxp:security:permission:translations';
    case Users                 = 'opendxp:security:permission:users';
    case WebsiteSettings       = 'opendxp:security:permission:website_settings';
    case WorkflowDetails       = 'opendxp:security:permission:workflow_details';
    case Notifications         = 'opendxp:security:permission:notifications';
    case NotificationsSend     = 'opendxp:security:permission:notifications_send';
    case Sites                 = 'opendxp:security:permission:sites';
    case ObjectsSortMethod     = 'opendxp:security:permission:objects_sort_method';
    case Objectbricks          = 'opendxp:security:permission:objectbricks';
    case Fieldcollections      = 'opendxp:security:permission:fieldcollections';
    case QuantityValueUnits    = 'opendxp:security:permission:quantityValueUnits';
    case Classificationstore   = 'opendxp:security:permission:classificationstore';
    case MaintenanceMode       = 'opendxp:security:permission:maintenance_mode';
}
