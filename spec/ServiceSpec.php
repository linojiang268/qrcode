<?php
namespace spec\Ouarea\Qrcode;

use PhpSpec\ObjectBehavior;

class ServiceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf(\Ouarea\Qrcode\Service::class);
    }

    //=====================================
    //          Generate
    //=====================================
    function it_generates_simple_qrcode()
    {
        $this->generate('Go for it, icarowner', [
            'size'           => 300,
            'margin'         => 10,
            'save_as_format' => 'png',
        ])->shouldBePngString();
    }

    function it_generates_qrcode_with_logo()
    {
        $this->generate('Go for it, icarowner', [
            'size'   => 300,
            'logo'   => __DIR__ . '/data/twitter.png',
            'margin' => 10,
        ])->shouldBePngString();
    }

    function it_generates_qrcode_and_save_it()
    {
        $saveAs = $this->createTempFilename();
        $this->generate('Go for it, icarowner', [
            'size'           => 280,
            'logo'           => __DIR__ . '/data/twitter.png',
            'margin'         => 10,
            'save_as_path'   => $saveAs,
            'save_as_format' => 'png', // explicitly give the save as format
        ])->shouldEqual(true);

        \PHPUnit_Framework_Assert::assertTrue(file_exists($saveAs), 'qrcode should be saved');

        // check qrcode size
        list($width, $height) = getimagesize($saveAs);
        \PHPUnit_Framework_Assert::assertEquals(300, $width,  'width of the qrcode should be 300');
        \PHPUnit_Framework_Assert::assertEquals(300, $height, 'height of the qrcode should be 300');
        \PHPUnit_Framework_Assert::assertTrue($this->isPngString(file_get_contents($saveAs)), 'qrcode should be in PNG format');

        unlink($saveAs); // clean up
    }

    function it_generates_qrcode_and_save_it_deduce_the_format()
    {
        $saveAs = $this->createTempFilename(). '.png'; // append the format so that it can be deduced
        $this->generate('Go for it, icarowner', [
            'size'         => 480,
            'logo'         => __DIR__ . '/data/twitter.png',
            'margin'       => 10,
            'save_as_path' => $saveAs,
        ])->shouldEqual(true);

        \PHPUnit_Framework_Assert::assertTrue(file_exists($saveAs), 'qrcode should be saved');
        \PHPUnit_Framework_Assert::assertTrue($this->isPngString(file_get_contents($saveAs)),
                                             'qrcode should be in PNG format');

        unlink($saveAs); // clean up
    }

    function it_generates_qrcode_and_scale_the_logo()
    {
        $saveAs = $this->createTempFilename() . '.png';
        $this->generate('Go for it, icarowner', [
            'size'             => 400,
            'logo'             => __DIR__ . '/data/twitter.png',
            'logo_scale_width' => 32,    // scale it to 32 x 32
            'margin'           => 10,
            'save_as_path'     => $saveAs,

        ])->shouldEqual(true);
        \PHPUnit_Framework_Assert::assertTrue(file_exists($saveAs), 'qrcode should be saved');
        \PHPUnit_Framework_Assert::assertTrue($this->isPngString(file_get_contents($saveAs)),
                                              'qrcode should be in PNG format');

        unlink($saveAs); // clean up
    }

    public function getMatchers()
    {
        return [
            'bePngString' => function ($image) {
                return $this->isPngString($image);
            },
        ];
    }

    private function isPngString($image)
    {
        if (!is_string($image)) {
            return false;
        }

        // check the string by inspecting its first 4 letters
        return (bin2hex($image[0]) == '89' &&
            $image[1] == 'P' && $image[2] == 'N' && $image[3] == 'G');
    }

    private function createTempFilename($prefix = 'qrcode')
    {
        return sys_get_temp_dir() . '/' .  uniqid($prefix);
    }
}
