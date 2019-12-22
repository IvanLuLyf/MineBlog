<?php
return [
    'filter' => 'admin',
    'path' => 'admin',
    'navs' => [
        ['name' => '博客管理', 'model' => 'blog'],
        ['name' => '用户管理', 'model' => 'user'],
    ],
    'models' => [
        'blog' => [
            'title' => '博客',
            'pk' => 'tid',
            'view' => [
                'tid' => ['label' => '编号', 'type' => 'number'],
                'title' => ['label' => '标题', 'type' => 'text'],
                'username' => ['label' => '作者', 'type' => 'text'],
                'visible' => ['label' => '可见', 'type' => 'enum', 'enum' => [
                    0 => '全部',
                    1 => '自己'
                ]],
                'timestamp' => ['label' => '时间', 'type' => 'timestamp'],
            ],
            'edit' => [
                'tid' => ['label' => '编号', 'type' => 'number'],
                'title' => ['label' => '标题', 'type' => 'text'],
                'username' => ['label' => '作者', 'type' => 'text'],
                'content' => ['label' => '内容', 'type' => 'textarea'],
                'visible' => ['label' => '可见', 'type' => 'enum', 'enum' => [
                    0 => '全部',
                    1 => '自己'
                ]]
            ],
            'add' => [
                'title' => ['label' => '标题', 'type' => 'text'],
                'username' => ['label' => '作者', 'type' => 'text'],
                'content' => ['label' => '内容', 'type' => 'textarea'],
                'visible' => ['label' => '可见', 'type' => 'enum', 'enum' => [
                    0 => '全部',
                    1 => '自己'
                ]]
            ],
        ],
        'user' => [
            'title' => '用户',
            'pk' => 'uid',
            'column' => [
                'uid' => ['label' => '用户ID', 'type' => 'number'],
                'username' => ['label' => '用户名', 'type' => 'text'],
                'email' => ['label' => '邮箱', 'type' => 'text'],
            ]
        ],
    ]
];