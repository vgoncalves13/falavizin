<?php

namespace App\Services;

use App\Models\Business;
use FPDF;

class BusinessPosterService
{
    private const W = 1240;

    private const H = 1748;

    /** @var array<int, int> */
    private const BLUE = [16, 70, 187];

    /** @var array<int, int> */
    private const BLUE_DARK = [11, 50, 140];

    /** @var array<int, int> */
    private const ORANGE = [200, 79, 0];

    /** @var array<int, int> */
    private const YELLOW = [255, 184, 0];

    /** @var array<int, int> */
    private const BG = [255, 250, 244];

    /** @var array<int, int> */
    private const TEXT = [32, 32, 32];

    /** @var array<int, int> */
    private const MUTED = [118, 95, 85];

    /** @var array<int, int> */
    private const BORDER = [234, 223, 212];

    /** @var array<int, int> */
    private const TAGLINE = [177, 153, 142];

    /** @var array<int, int> */
    private const WHITE = [255, 255, 255];

    /** @var array<int, int> */
    private const SCAN_BG = [233, 238, 252];

    /** @var array<int, int> */
    private const FOUNDER_BG = [255, 246, 222];

    /** @var array<int, int> */
    private const FOUNDER_BORDER = [236, 197, 158];

    public function __construct(private BusinessQrCodeService $qr) {}

    public function pngFor(Business $business): string
    {
        $img = imagecreatetruecolor(self::W, self::H);
        imagesavealpha($img, true);

        $this->fill($img, self::BG);

        $y = $this->compose($img, $business);

        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();

        return $png;
    }

    public function pdfFor(Business $business): string
    {
        $pdf = new FPDF('P', 'mm', [105, 148]); // A6 retrato
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $tmp = tempnam(sys_get_temp_dir(), 'falavizin-poster-');
        if ($tmp === false) {
            throw new \RuntimeException('Não foi possível criar arquivo temporário para o poster.');
        }

        file_put_contents($tmp, $this->pngFor($business));
        $pdf->Image($tmp, 0, 0, 105, 148, 'PNG');

        unlink($tmp);

        return $pdf->Output('S');
    }

    public function inlineFor(Business $business): string
    {
        return 'data:image/png;base64,'.base64_encode($this->pngFor($business));
    }

    private function compose(\GdImage $img, Business $business): int
    {
        // barras superiores da marca
        $this->rect($img, 0, 0, (int) round(self::W * 0.58), 30, self::BLUE);
        $this->rect($img, (int) round(self::W * 0.58), 0, (int) round(self::W * 0.42), 30, self::ORANGE);

        // logotipo
        $y = 96;
        $logoWidth = 130;
        $logoHeight = 130;
        $logoLockupWidth = $this->brandLockupWidth();
        $lockupX = (int) ((self::W - $logoLockupWidth) / 2);
        $this->image($img, $lockupX, $y, $logoWidth, $logoHeight, public_path('assets/icons/icon-192.png'));
        $this->text($img, resource_path('fonts/Inter-ExtraBold.ttf'), 58, self::BLUE_DARK, 'FalaVizin',
            $lockupX + $logoWidth + 34, $y + intdiv($logoHeight, 2), 0, 'left');

        $y += $logoHeight + 48;

        // estrelas
        $starSize = 40;
        $gap = 14;
        $totalStars = 5 * $starSize + 4 * $gap;
        $starCenterX = (int) ((self::W - $totalStars) / 2) + intdiv($starSize, 2);
        for ($i = 0; $i < 5; $i++) {
            $this->star($img, $starCenterX + $i * ($starSize + $gap), $y, intdiv($starSize, 2), self::YELLOW);
        }

        $y += $starSize + 38;

        // headline
        $headline = 'Sua recomendação ajuda o';
        $highlight = 'comércio do bairro';
        $headlineSize = 60;
        $this->text($img, resource_path('fonts/Lora-Bold.ttf'), $headlineSize, self::TEXT, $headline, self::W / 2, $y, 0, 'center');
        $y += (int) round($headlineSize * 1.24);
        $this->text($img, resource_path('fonts/Lora-Bold.ttf'), $headlineSize, self::ORANGE, $highlight, self::W / 2, $y, 0, 'center');

        $y += (int) round($headlineSize * 1.24) + 14;

        // descrição
        $descriptionLines = $this->wrap(
            resource_path('fonts/Inter-Medium.ttf'),
            28,
            'Escaneie o código e conte sua experiência para outros vizinhos.',
            820,
        );
        foreach ($descriptionLines as $line) {
            $this->text($img, resource_path('fonts/Inter-Medium.ttf'), 28, self::MUTED, $line, self::W / 2, $y, 0, 'center');
            $y += 40;
        }

        $y += 22;

        // divisor
        $this->rect($img, 84, $y, self::W - 168, 4, self::BORDER);
        $y += 56;

        // container do QR
        $qrBox = 560;
        $qrX = (int) ((self::W - $qrBox) / 2);
        $this->roundedRect($img, $qrX + 12, $y + 12, $qrBox, $qrBox, 50, [231, 222, 210]);
        $this->roundedRect($img, $qrX, $y, $qrBox, $qrBox, 50, self::WHITE, self::BORDER, 4);

        $qrPng = $this->qr->pngFor($business);
        $padding = 52;
        $this->imageFromString($img, $qrX + $padding, $y + $padding, $qrBox - $padding * 2, $qrBox - $padding * 2, $qrPng);

        $y += $qrBox + 58;

        // pill "aponte a câmera"
        $scanText = 'APONTE A CÂMERA';
        $scanFont = resource_path('fonts/Inter-ExtraBold.ttf');
        $scanSize = 27;
        $scanTextWidth = $this->textWidth($scanFont, $scanSize, $scanText);
        $pillWidth = (int) round($scanTextWidth + 80);
        $pillHeight = 64;
        $pillX = (int) ((self::W - $pillWidth) / 2);
        $this->roundedRect($img, $pillX, $y, $pillWidth, $pillHeight, intdiv($pillHeight, 2), self::SCAN_BG, [193, 206, 240], 2);

        $this->text($img, $scanFont, $scanSize, self::BLUE_DARK, $scanText, self::W / 2, $y + intdiv($pillHeight, 2), 0, 'center');

        $y += $pillHeight + 50;

        // divisor
        $this->rect($img, 84, $y, self::W - 168, 4, self::BORDER);
        $y += 72;

        // nome do estabelecimento
        $nameLines = $this->wrap(resource_path('fonts/Inter-ExtraBold.ttf'), 38, mb_strtoupper($business->name), 940, 2);
        foreach ($nameLines as $line) {
            $this->text($img, resource_path('fonts/Inter-ExtraBold.ttf'), 38, self::BLUE_DARK, $line, self::W / 2, $y, 0, 'center');
            $y += 50;
        }

        $y += 16;

        // selo de fundador (condicional)
        if ($business->is_founder) {
            $badgeText = 'COMÉRCIO FUNDADOR';
            $badgeFont = resource_path('fonts/Inter-ExtraBold.ttf');
            $badgeSize = 23;
            $badgeTextWidth = $this->textWidth($badgeFont, $badgeSize, $badgeText);
            $badgeStar = 30;
            $badgeWidth = (int) round($badgeTextWidth + $badgeStar + 76);
            $badgeHeight = 48;
            $badgeX = (int) ((self::W - $badgeWidth) / 2);
            $this->roundedRect($img, $badgeX, $y, $badgeWidth, $badgeHeight, intdiv($badgeHeight, 2), self::FOUNDER_BG, self::FOUNDER_BORDER, 2);

            $starCx = $badgeX + 36;
            $this->star($img, $starCx, $y + intdiv($badgeHeight, 2), 14, self::YELLOW);

            $this->text($img, $badgeFont, $badgeSize, self::ORANGE, $badgeText,
                $badgeX + 36 + $badgeStar + 14, $y + intdiv($badgeHeight, 2), 0, 'left');

            $y += $badgeHeight + 34;
        } else {
            $y += 34;
        }

        // tagline
        $this->text($img, resource_path('fonts/Lora-Italic.ttf'), 26, self::TAGLINE, 'O bairro fala. Você fica sabendo.', self::W / 2, $y, 0, 'center');

        $y += 40;

        // marcas de rodapé
        $markWidth = 128;
        $markHeight = 13;
        $gap = 18;
        $marksTotal = $markWidth * 2 + $gap;
        $markX = (int) ((self::W - $marksTotal) / 2);
        $this->roundedRect($img, $markX, $y, $markWidth, $markHeight, intdiv($markHeight, 2), self::BLUE);
        $this->roundedRect($img, $markX + $markWidth + $gap, $y, $markWidth, $markHeight, intdiv($markHeight, 2), self::ORANGE);

        return $y;
    }

    private function brandLockupWidth(): int
    {
        return 145 + 34 + (int) round($this->textWidth(resource_path('fonts/Inter-ExtraBold.ttf'), 58, 'FalaVizin'));
    }

    /** @return list<string> */
    private function wrap(string $font, int $size, string $text, int $maxWidth, ?int $maxLines = null): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if ($this->textWidth($font, $size, $candidate) > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;

                if ($maxLines !== null && count($lines) === $maxLines) {
                    break;
                }
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '' && ($maxLines === null || count($lines) < $maxLines)) {
            $lines[] = $current;
        }

        return $lines ?: [''];
    }

    private function textWidth(string $font, int $size, string $text): float
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return $box[2] - $box[0];
    }

    private function text(\GdImage $img, string $font, int $size, array $color, string $text, float $x, float $y, int $angle = 0, string $align = 'center'): void
    {
        $fontColor = $this->alloc($img, $color);
        $box = imagettfbbox($size, $angle, $font, $text);

        $tx = (float) $x;
        $ty = (float) $y;

        if ($align === 'center') {
            $tx -= ($box[2] - $box[0]) / 2;
        } elseif ($align === 'right') {
            $tx -= ($box[2] - $box[0]);
        }

        // centraliza a caixa de texto verticalmente em torno de $y
        $ty -= ($box[1] + $box[7]) / 2;

        imagettftext($img, $size, $angle, (int) round($tx), (int) round($ty), $fontColor, $font, $text);
    }

    /** @param array<int, int> $color */
    private function fill(\GdImage $img, array $color): void
    {
        imagefill($img, 0, 0, $this->alloc($img, $color));
    }

    /** @param array<int, int> $color */
    private function rect(\GdImage $img, int $x, int $y, int $w, int $h, array $color): void
    {
        imagefilledrectangle($img, $x, $y, $x + $w - 1, $y + $h - 1, $this->alloc($img, $color));
    }

    /**
     * @param  array<int, int>  $color
     * @param  array<int, int>|null  $border
     */
    private function roundedRect(\GdImage $img, int $x, int $y, int $w, int $h, int $radius, array $color, ?array $border = null, int $borderWidth = 1): void
    {
        if ($border !== null) {
            $this->roundedRect($img, $x - $borderWidth, $y - $borderWidth, $w + $borderWidth * 2, $h + $borderWidth * 2, $radius + $borderWidth, $border);
        }

        $r = min($radius, (int) floor($w / 2), (int) floor($h / 2));
        $fill = $this->alloc($img, $color);

        imagefilledrectangle($img, $x + $r, $y, $x + $w - $r - 1, $y + $h - 1, $fill);
        imagefilledrectangle($img, $x, $y + $r, $x + $w - 1, $y + $h - $r - 1, $fill);

        // cantos
        imagefilledellipse($img, $x + $r, $y + $r, $r * 2, $r * 2, $fill);
        imagefilledellipse($img, $x + $w - $r - 1, $y + $r, $r * 2, $r * 2, $fill);
        imagefilledellipse($img, $x + $r, $y + $h - $r - 1, $r * 2, $r * 2, $fill);
        imagefilledellipse($img, $x + $w - $r - 1, $y + $h - $r - 1, $r * 2, $r * 2, $fill);
    }

    /** @param array<int, int> $color */
    private function star(\GdImage $img, int $cx, int $cy, int $outer, array $color): void
    {
        $inner = (int) round($outer * 0.45);
        $points = [];
        $count = 10;

        for ($i = 0; $i < $count; $i++) {
            $radius = $i % 2 === 0 ? $outer : $inner;
            $angle = (M_PI * 2 * $i / $count) - (M_PI / 2);
            $points[] = (int) round($cx + $radius * cos($angle));
            $points[] = (int) round($cy + $radius * sin($angle));
        }

        imagefilledpolygon($img, $points, $this->alloc($img, $color));
    }

    private function image(\GdImage $img, int $x, int $y, int $w, int $h, string $path): void
    {
        $src = @imagecreatefromstring((string) file_get_contents($path));

        if ($src === false) {
            return;
        }

        imagecopyresampled($img, $src, $x, $y, 0, 0, $w, $h, imagesx($src), imagesy($src));
    }

    private function imageFromString(\GdImage $img, int $x, int $y, int $w, int $h, string $data): void
    {
        $src = @imagecreatefromstring($data);

        if ($src === false) {
            return;
        }

        imagecopyresampled($img, $src, $x, $y, 0, 0, $w, $h, imagesx($src), imagesy($src));
    }

    /** @param array<int, int> $color */
    private function alloc(\GdImage $img, array $color): int
    {
        return imagecolorallocate($img, $color[0], $color[1], $color[2]);
    }
}
