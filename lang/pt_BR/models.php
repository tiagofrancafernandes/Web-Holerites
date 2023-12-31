<?php

return [
    'general' => [
        'id' => 'ID',
        'created_at' => 'Creatad em',
        'updated_at' => 'Atualizado em',
        'deleted_at' => 'Inativado em',
    ],
    'Document' => [
        'modelLabel' => 'Documento',
        'titleCaseModelLabel' => 'Documento',
        'pluralModelLabel' => 'Documentos',
        'actions' => [
            'create' => 'Cadastrar novo documento',
        ],
        'table' => [
            'id' => 'ID',
            'title' => 'Título',
            'note' => 'Nota',
            'status' => 'Status',
            'release_date' => 'Disponível a partir de',
            'available_until' => 'Disponível até',
            'internal_note' => 'Nota interna',
            'public_note' => 'Nota pública',
            'storage_file_id' => 'Arquivo',
            'created_at' => 'Creatad em',
            'updated_at' => 'Atualizado em',
            'deleted_at' => 'Inativado em',
        ],
    ],
    'DocumentCategory' => [
        'modelLabel' => 'Categoria',
        'titleCaseModelLabel' => 'Categoria',
        'pluralModelLabel' => 'Categorias',
        'actions' => [
            'create' => 'Cadastrar nova categoria',
        ],
        'table' => [
            'id' => 'ID',
            'parent_id' => 'Categoria pai',
            'parent_name' => 'Categoria pai',
            'name' => 'Nome',
            'slug' => 'Slug',
            'description' => 'Descrição',
            'seo_title' => 'SEO - Título',
            'seo_description' => 'SEO - Descrição',
            'created_at' => 'Creatad em',
            'updated_at' => 'Atualizado em',
            'deleted_at' => 'Inativado em',
        ],
        'form' => [
            'parent_id' => 'Categoria pai',
            'parent_name' => 'Categoria pai',
            'name' => 'Nome',
            'slug' => 'Slug',
            'description' => 'Descrição',
            'seo_title' => 'SEO - Título',
            'seo_description' => 'SEO - Descrição',
        ],
        'filters' => [
            'status' => 'Status',
        ],
    ],
];
