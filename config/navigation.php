<?php

return [
    'header' => [
        ['label' => 'О нас', 'route' => 'about'],
        ['label' => 'Генплан', 'route' => 'genplan.index'],
        ['label' => 'Выбрать участок', 'route' => null],
        ['label' => 'Контакты', 'route' => 'contacts'],
    ],

    'footer' => [
        'Главная' => [
            ['label' => 'Территория', 'route' => null],
            ['label' => 'Ген план', 'route' => 'genplan.index'],
            ['label' => 'Выбрать участок', 'route' => null],
            ['label' => 'Варианты приобретения', 'route' => null],
        ],
        'Партнёрам' => [
            ['label' => 'О нас', 'route' => 'about'],
            ['label' => 'Контакты', 'route' => 'contacts'],
            ['label' => 'Блог', 'route' => 'blog.index'],
        ],
    ],
];
