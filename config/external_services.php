<?php

return [
    'cadsus_api_url' => env('CADSUS_API_URL', 'https://cns-api.azurewebsites.net/api/'),
    'cadsus_api_token' => env('CADSUS_API_KEY'),
    'cnes_api_url' => env('CNES_API_URL', 'https://apidadosabertos.saude.gov.br/cnes/'),
    'rastro_system_api_url' => env('RASTRO_SYSTEM_API_URL', 'http://rastronet.rastrosystem.com.br/api_v2/'),
    'rastro_system_api_username' => env('RASTRO_SYSTEM_API_USERNAME'),
    'rastro_system_api_password' => env('RASTRO_SYSTEM_API_PASSWORD'),
    'zapi_url' => env('ZAPI_URL', 'https://api.z-api.io/instances/390729792F1650CE884E8E754518BA1D/token/090ACFD757AD43E23B4821A1'),
    'zapi_client_token' => env('ZAPI_CLIENT_TOKEN', 'F8cdd8d784ac64ecd8cf7a63fdac6eaf3S'),
    'zapi_security_token' => env('ZAPI_SECURITY_TOKEN', 'F8cdd8d784ac64ecd8cf7a63fdac6eaf3S'),
    'google_maps_api_url' => 'https://maps.googleapis.com/maps/api/',
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
];
