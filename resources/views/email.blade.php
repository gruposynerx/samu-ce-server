<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: "Open Sans", sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
    </style>
</head>
    <body style="background-image: url('https://portal.samu360.com.br/media/logos/fundo-samu.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 500px; margin: 0 auto;">
            <thead>
                <td style="padding-top: 1rem;">
                </td>
            </thead>
            <tr>
                <td style="background-color: #FFFFFF; padding: 0 1.8rem 1.8rem 1.8rem; border-radius: 3px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                        <tr>
                            <td style="text-align: center; padding-top: 0.6rem;">
                                <img src="https://portal.samu360.com.br/media/logos/samu-logo.png" alt="Logo" style="max-width: 60%;">
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-top: 0.3rem;">
                                <p style="margin: 0; padding: 0; font-size: 30px; font-weight: 700;">Olá {{ $userName }},</p>
                                <p style="font-size: 14px; margin: 10px 0;">Você está recebendo este e-mail porque recebemos uma solicitação de <span style="font-weight: bold;">redefinição de senha para sua conta</span></p>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: center;">
                                <a href={{ $url }} style="margin: 1rem; display: inline-block; border: none; border-radius: 5px; background-color: #D52128; color: #FFFFFF; padding: 10px 22px; font-size: 14px; font-weight: 600; text-decoration: none;">Modificar Senha</a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <p style="font-size: 14px; margin: 10px 0;">Este link de redefinição de senha expirará em <span style="font-weight: bold;">60 minutos.</span></p>
                                <p style="font-size: 14px; margin: 10px 0;">Se você não solicitou a redefinição de senha, nenhuma ação será necessária.</p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px">
                                <p style="font-size: 14px; margin: 0;">Saudações,</p>
                                <p style="font-size: 14px; margin: 0; margin-bottom: 1rem;">Samu360</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <hr>
                                <p style="font-size: 12px; padding-top: 0.5rem;">Se você estiver com problemas para clicar no botão "Modificar Senha", copie e cole a URL abaixo em seu navegador da web: <a href={{ $url }} style="font-size: 12px; color: #C5250D; margin: 0; word-break: break-all;;">{{ $url }}</a></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tfoot>
                <tr>
                    <td style="text-align: center; margin-top: 1rem;">
                        <p style="font-size: 13px; color: #FFFFFF; font-weight: 700;">&copy; 2024 Samu360. Todos os direitos reservados.</p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
