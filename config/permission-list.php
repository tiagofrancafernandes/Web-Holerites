<?php

/**
 * [EM TESTE]
 */

return [
    'generic' => [
        'feedback::can_submit',
    ],
    'article' => [
        'article::edit',
        'article::delete',
        'article::publish',
        'article::unpublish',
    ],
    'user' => [
        'user::create',
        'user::edit',
        'user::update',
        'user::list',
        'user::listAll',
        'user::delete',
    ],
    'employee' => [
        'employee::create',
        'employee::edit',
        'employee::update',
        'employee::list',
        'employee::listAll',
        'employee::delete',
        'employee::publish',
        'employee::unpublish',
    ],
    'document' => [
        'document::create',
        'document::edit',
        'document::update',
        'document::list',
        'document::listAll',
        'document::delete',
        'document::publish',
        'document::unpublish',
    ],
    'team' => [
        'team::create',
        'team::edit',
        'team::update',
        'team::list',
        'team::listAll',
        'team::delete',
    ],
    'painel' => [
        'painel::access',
    ],
    'tenant-management' => [
        'tenant-list::view',
        'impersonate::tenant',
    ],

    /**
     * ! CAUTION !
     * 'global_permissions' arw shared permissions for all users
     * All user will has this persmissions
     * Use only for non secure permissions
     */
    'global_permissions' => [
        'feedback::can_submit',
    ],
    'default_suffix' => [
        'view',
        'viewAny',
        'create',
        'edit',
        'editAny',
        'update',
        'updateAny',
        'delete',
        'deleteAny',
        'forceDelete',
        'forceDeleteAny',
        'restore',
        'restoreAny',
        'reorder',
        'reorderAny',
    ],
];
