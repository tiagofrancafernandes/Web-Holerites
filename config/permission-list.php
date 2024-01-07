<?php

/**
 * [EM TESTE]
 */

return [
    'generic' => [
        'feedback::can_submit',
        'permission::can_attach',
        'permission::can_detach',
        'group::can_attach',
        'group::can_detach',
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
        'document::manage',
        'document::create',
        'document::edit',
        'document::update',
        'document::list',
        'document::listAll',
        'document::delete',
        'document::publish',
        'document::unpublish',
    ],
    'document_status' => [
        'document_status::see.DRAFT',
        'document_status::see.INVALID',
        'document_status::see.VALIDATED',
        'document_status::see.UNDER_ANALYSIS',
        'document_status::see.REJECTED',
        'document_status::see.APPROVED_FOR_PUBLICATION',
        'document_status::see.AWAITING_REVIEW',
        'document_status::see.PUBLISHED',
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
        // !! Any permission here will apply to all users
        'feedback::can_submit',
    ],
    'default_suffix' => [
        'list',
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
