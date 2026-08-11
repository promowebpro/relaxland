<?php

return [
    'header' => [
        ['label' => 'О нас', 'route' => null],
        ['label' => 'Генплан', 'route' => null],
        ['label' => 'Выбрать участок', 'route' => null],
        ['label' => 'Контакты', 'route' => 'contacts'],
    ],

    'footer' => [
        'Главная' => [
            ['label' => 'Территория', 'route' => null],
            ['label' => 'Ген план', 'route' => null],
            ['label' => 'Выбрать участок', 'route' => null],
            ['label' => 'Варианты приобретения', 'route' => null],
        ],
        'Партнёрам' => [
            ['label' => 'О нас', 'route' => null],
            ['label' => 'Контакты', 'route' => 'contacts'],
            ['label' => 'Блог', 'route' => 'blog.index'],
        ],
    ],
];
