<?php

namespace App\Services;

use App\Models\Business;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRInterventionImage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\ImageManager;

class BusinessQrCodeService
{
    private const LOGO_SPACE = 13;

    public function contentFor(Business $business): string
    {
        return $business->canonicalUrl();
    }

    public function pngFor(Business $business): string
    {
        $options = new QROptions([
            'outputInterface' => QRInterventionImage::class,
            'version' => Version::AUTO,
            'versionMin' => 7,
            'eccLevel' => EccLevel::H,
            'scale' => 8,
            'outputBase64' => false,
            'bgColor' => '#ffffff',
            'addLogoSpace' => true,
            'logoSpaceWidth' => self::LOGO_SPACE,
            'logoSpaceHeight' => self::LOGO_SPACE,
            'moduleValues' => $this->moduleValues(),
        ]);

        $png = (new QRCode($options))->render($this->contentFor($business));

        return $this->overlayLogo($png);
    }

    public function inlineFor(Business $business): string
    {
        return 'data:image/png;base64,'.base64_encode($this->pngFor($business));
    }

    /** @return array<int, string> */
    private function moduleValues(): array
    {
        return [
            // escuros — tons âmbar da marca
            QRMatrix::M_DARKMODULE => '#78350f',
            QRMatrix::M_DATA_DARK => '#78350f',
            QRMatrix::M_ALIGNMENT_DARK => '#78350f',
            QRMatrix::M_TIMING_DARK => '#78350f',
            QRMatrix::M_SEPARATOR_DARK => '#78350f',
            QRMatrix::M_FORMAT_DARK => '#b45309',
            QRMatrix::M_VERSION_DARK => '#b45309',
            QRMatrix::M_FINDER_DARK => '#b45309',
            QRMatrix::M_FINDER_DOT => '#b45309',
            // claros — branco
            QRMatrix::M_NULL => '#ffffff',
            QRMatrix::M_DATA => '#ffffff',
            QRMatrix::M_ALIGNMENT => '#ffffff',
            QRMatrix::M_TIMING => '#ffffff',
            QRMatrix::M_SEPARATOR => '#ffffff',
            QRMatrix::M_FORMAT => '#ffffff',
            QRMatrix::M_VERSION => '#ffffff',
            QRMatrix::M_FINDER => '#ffffff',
            QRMatrix::M_FINDER_DOT_LIGHT => '#ffffff',
            QRMatrix::M_DARKMODULE_LIGHT => '#ffffff',
            QRMatrix::M_QUIETZONE => '#ffffff',
            QRMatrix::M_LOGO => '#ffffff',
        ];
    }

    private function overlayLogo(string $png): string
    {
        $logoPath = public_path('assets/icons/icon-192.png');

        if (! is_file($logoPath)) {
            return $png;
        }

        $manager = new ImageManager(new Driver);
        $qr = $manager->read($png);
        $size = $qr->width();

        $logoSize = (int) round($size * 0.20);
        $logo = $manager->read($logoPath)->resize($logoSize, $logoSize);

        $circleRadius = (int) round($logoSize * 0.55);

        $qr->drawCircle($size / 2, $size / 2, function (CircleFactory $circle) use ($circleRadius): void {
            $circle->radius($circleRadius);
            $circle->background('#ffffff');
        });

        $qr->place($logo, 'center');

        return $qr->toPng()->toString();
    }
}
