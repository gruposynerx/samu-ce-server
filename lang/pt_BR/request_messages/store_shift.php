<?php

return [
    'name' => [
        'required' => 'O nome do turno é obrigatório.',
        'string' => 'O nome do turno deve ser uma string.',
        'max' => 'O nome do turno não pode ter mais que 255 caracteres.',
        'unique' => 'Este nome de turno já está em uso.',
    ],
    'start_time' => [
        'required' => 'O horário de início é obrigatório.',
        'date_format' => 'O horário de início deve estar no formato HH:MM.',
    ],
    'start_time' => [
    'unique' => 'Já existe um turno com este horário de início e término.',
    ],
    'end_time' => [
        'required' => 'O horário de término é obrigatório.',
        'date_format' => 'O horário de término deve estar no formato HH:MM.',
    ],
    'next_day' => [
        'boolean' => 'O campo "próximo dia" deve ser verdadeiro ou falso.',
    ],
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 3357112d (rebase ajust)
=======

>>>>>>> 86c150cf (rebase ajust)
];
