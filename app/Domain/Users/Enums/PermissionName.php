<?php

namespace App\Domain\Users\Enums;

enum PermissionName: string
{
    case AdminAccess = 'admin.access';

    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case RolesView = 'roles.view';
    case RolesCreate = 'roles.create';
    case RolesUpdate = 'roles.update';
    case RolesDelete = 'roles.delete';

    case ContentView = 'content.view';
    case ContentCreate = 'content.create';
    case ContentUpdate = 'content.update';
    case ContentDelete = 'content.delete';
    case ContentPublish = 'content.publish';

    case LeadsView = 'leads.view';
    case LeadsUpdate = 'leads.update';

    case GenplanView = 'genplan.view';
    case GenplanManage = 'genplan.manage';

    case PlotsView = 'plots.view';
    case PlotsManage = 'plots.manage';

    case SettingsView = 'settings.view';
    case SettingsManage = 'settings.manage';
}
