<?php

return [
    'ticket_infos' => [
        'request_made' => [
            'headline' => "Olá _:user_ 👋\n\nSua solicitação para o *SAMU CEARÁ* 🚑 foi realizada com sucesso e gerou o(s) seguinte(s) protocolo(s):\n\n",
            'description' => "📋 Protocolo: *:protocol* \n",
            'footer' => "\n❓ *Deseja acompanhar o andamento da sua ocorrência e receber alertas?*👇",
            'buttons_list' => [
                'button_label' => 'Mais atualizações',
                'options' => [
                    [
                        'id' => 1,
                        'type' => 'URL',
                        'url' => ':url',
                        'label' => 'Ver ocorrência',
                    ],
                    // [
                    //     'id' => 2,
                    //     'type' => 'REPLY',
                    //     'label' => 'Receber alertas',
                    // ],
                    // [
                    //     'id' => 3,
                    //     'type' => 'REPLY',
                    //     'label' => 'Parar alertas',
                    // ],
                ],
            ],
        ],
        'vehicle_sent' => [
            'headline' => "Olá _:user_ 👋\n\nSua solicitação para o *SAMU CEARÁ* 🚑 possui uma *ambulância empenhada*:\n\n",
            'description' => "📋 Seu Protocolo é: *:protocol* \nEndereço: _:address_",
        ],
        'completed' => [
            'headline' => "Olá _:user_ 👋\n\nSua solicitação para o *SAMU CEARÁ* 🚑 foi *concluída*:\n\n",
            'description' => "📋 Seu Protocolo é: *:protocol* \nEndereço: _:address_",
        ],
        'canceled' => [
            'headline' => "Olá _:user_ 👋\n\nSua solicitação para o *SAMU CEARÁ* 🚑 foi *encerrada*:\n\n",
            'description' => "📋 Seu Protocolo é: *:protocol* \n\nEndereço: _:address_",
        ],
    ],
];
