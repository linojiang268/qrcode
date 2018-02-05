<?php

namespace Ouarea\Qrcode;

use Endroid\QrCode\QrCode;

/**
 * Service for generate qrcode
 */
class Service
{
    /**
     * generate qrcode
     *
     * @param string $content   the content, e.g., links, text, etc
     * @param array $options    options (include but not limited to):
     *                           - size              the width/height the qrcode. (defaults to 280)
     *                           - margin            the margin of qrcode. (defaults to 10)
     *                           - ecl               (optional)error correction level. might be one of
     *                                               * 'high'     (default)  up to 30% damage
     *                                               * 'quartile' up to 25% damage
     *                                               * 'medium'   up to 15% damage
     *                                               * 'low'      up to 7% damage
     *                           - logo              path to an image that's supposed to be put in
     *                                               the middle of the generated qrcode
     *                           - logo_scale_width  the new width the logo should be scaled to
     *                           - fgcolor           foreground color (in RRGGBBAA string), default to '00000000'
     *                           - bgcolor           background color (in RRGGBBAA string), default to 'FFFFFF00'
     *                           - save_as_path      file path this generated qrcode will be saved as
     *                           - save_as_format    the format of the save as file or return data. (defaults to 'png')
     *                           - validate_result   boolean
     *
     * @return bool|string  if save_as option is enabled, a boolean indicating the success or failure will be returned.
     *                      otherwise, the binary string(NOT stream) will be returned.
     */
    public function generate($content, array $options = [])
    {
        $qrcode = new QrCode($content);
        $qrcode->setSize(array_get($options, 'size', 280));
        $qrcode->setMargin(array_get($options, 'margin', 10));
        $qrcode->setForegroundColor($this->parseColor(array_get($options, 'fgcolor', '00000000')));
        $qrcode->setBackgroundColor($this->parseColor(array_get($options, 'bgcolor', 'FFFFFF00')));
        $qrcode->setValidateResult(array_get($options, 'validate_result', false));

        ($ecl = array_get($options, 'ecl')) && $qrcode->setErrorCorrectionLevel($ecl);

        if ($logo = array_get($options, 'logo')) { // logo given
            $qrcode->setLogoPath($logo);

            ($widthOfLogo = array_get($options, 'logo_scale_width')) && $qrcode->setLogoWidth($widthOfLogo);
        }

        ($saveAsFormat = array_get($options, 'save_as_format')) && $qrcode->setWriterByName($saveAsFormat);

        if ($saveAsPath = array_get($options, 'save_as_path')) { // save as file specified
            $qrcode->writeFile($saveAsPath);
            return true;
        }

        return $qrcode->writeString();
    }

    // convert 'RRGGBBAA' color to ['r' => RR, 'g' => GG, 'b' => BB, 'a' => AA]
    private function parseColor($color)
    {
        return [
            'r' => intval(substr($color, 0, 2), 16),
            'g' => intval(substr($color, 2, 2), 16),
            'b' => intval(substr($color, 4, 2), 16),
            'a' => intval(substr($color, 6, 2), 16),
        ];
    }
}