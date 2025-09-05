<?php

return [
    'ticket_type_id.required' => 'É obrigatório informar o tipo de chamado.',
    'ticket_type_id.numeric' => 'O tipo de chamado deve ser um número.',
    'ticket_type_id.in' => 'O tipo de chamado deve ser diferente de primário e secundário.',
    'opening_at.required' => 'É obrigatório informar a data de abertura do chamado.',
    'opening_at.date' => 'A data de abertura do chamado deve ser uma data válida.',
    'city_id.numeric' => 'A cidade deve ser um número.',
    'city_id.exists' => 'A cidade não existe.',
    'requester.name.required' => 'É obrigatório informar o nome do solicitante.',
    'requester.name.string' => 'O nome do solicitante deve ser uma string.',
    'requester.primary_phone.string' => 'O telefone primário do solicitante deve ser uma string.',
    'requester.secondary_phone.string' => 'O telefone secundário do solicitante deve ser uma string.',
    'patients.name.string' => 'O nome do paciente deve ser uma string.',
    'patients.age.integer' => 'A idade do paciente deve ser um número.',
    'patients.time_unit_id.integer' => 'A unidade de tempo da idade do paciente deve ser um número.',
    'description.string' => 'A descrição deve ser uma string.',
    'description.max' => 'A descrição deve ter no máximo 3000 caracteres.',
];
