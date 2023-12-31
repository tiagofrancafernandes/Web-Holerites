<?php

return [
    'general' => [
        'id' => 'ID',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
        'deleted_at' => 'Deleted at',
    ],
    'Document' => [
        'modelLabel' => 'Document',
        'titleCaseModelLabel' => 'Document',
        'pluralModelLabel' => 'Documents',
        'actions' => [
            'create' => 'Create new document',
        ],
        'table' => [
            'id' => 'ID',
            'title' => 'Title',
            'note' => 'Note',
            'status' => 'Status',
            'release_date' => 'Release date',
            'available_until' => 'Available until',
            'internal_note' => 'Internal note',
            'public_note' => 'Public note',
            'storage_file_id' => 'File',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
        ],
        'filters' => [
            'status' => 'Status',
        ],
    ],
    'DocumentCategory' => [
        'modelLabel' => 'Document category',
        'titleCaseModelLabel' => 'Document category',
        'pluralModelLabel' => 'Document categories',
        'actions' => [
            'create' => 'Create new document category',
        ],
        'table' => [
            'id' => 'ID',
            'parent_id' => 'Parent category',
            'parent_name' => 'Parent category',
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
        ],
        'form' => [
            'parent_id' => 'Parent category',
            'parent_name' => 'Parent category',
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
        ],
        'filters' => [
            'status' => 'Status',
        ],
    ],
];
