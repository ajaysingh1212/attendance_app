<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class MediaConversionCompatibilityTest extends TestCase
{
    public function test_spatie_image_crop_conversion_uses_v3_enum(): void
    {
        $output = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'clocking-spatie-fit-test.jpg';

        Image::load(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'logo.jpg')
            ->fit(Fit::Crop, 50, 50)
            ->save($output);

        $this->assertFileExists($output);
        [$width, $height] = getimagesize($output);
        $this->assertSame(50, $width);
        $this->assertSame(50, $height);

        unlink($output);
    }
}
