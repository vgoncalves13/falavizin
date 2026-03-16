<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reivindicar {{ $business->name }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #1c1917; background: #fafaf9; padding: 20px;">
    <div style="max-width: 560px; margin: 0 auto; background: white; border-radius: 12px; border: 1px solid #e7e5e4; padding: 32px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #1c1917; margin-bottom: 8px;">
            Confirme a reivindicação
        </h1>
        <p style="color: #78716c; margin-bottom: 24px;">
            Você solicitou a reivindicação do negócio <strong>{{ $business->name }}</strong> no Hub do Bairro.
        </p>
        <p style="margin-bottom: 24px;">
            Clique no botão abaixo para confirmar e passar a gerenciar este perfil:
        </p>
        <a
            href="{{ route('businesses.claim.verify', $business->claim_token) }}"
            style="display: inline-block; background: #d97706; color: white; font-weight: 600; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 15px;"
        >
            Confirmar reivindicação
        </a>
        <p style="margin-top: 24px; font-size: 13px; color: #a8a29e;">
            Se você não solicitou isso, ignore este email.
        </p>
        <hr style="margin: 24px 0; border: none; border-top: 1px solid #e7e5e4;">
        <p style="font-size: 12px; color: #a8a29e; margin: 0;">
            Hub do Bairro — Seu bairro conectado.
        </p>
    </div>
</body>
</html>
