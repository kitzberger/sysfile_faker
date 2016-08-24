<?php
namespace Kitzberger\SysfileFaker;

class FileReference extends \TYPO3\CMS\Core\Resource\FileReference
{
    /**
     * Constructor for a file in use object. Should normally not be used
     * directly, use the corresponding factory methods instead.
     *
     * @param array $fileReferenceData
     * @param ResourceFactory $factory
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $fileReferenceData, $factory = null)
    {
        parent::__construct($fileReferenceData, $factory);

        if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissing']) &&
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissing']) {
            $this->createFakeFileIfMissing();
        }
    }

    protected function createFakeFileIfMissing()
    {
        //var_dump($this->originalFile);

        $file = PATH_site . $this->getPublicUrl();
        if (!file_exists($file)) {
            //var_dump('Faking: ' . PATH_site . $this->getPublicUrl());
            //var_dump($this->getProperties());

            // create folder if necessary
            $folder = dirname(PATH_site . $this->getPublicUrl());
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $props = $this->getProperties();

            $text = $props['width'] . 'x' . $props['height'];
            $image = imagecreatetruecolor($props['width'], $props['height']);
            $black = imagecolorallocate($image, 0, 0, 0);
            $white = imagecolorallocate($image, 255, 255, 255);

            // Draw a white rectangle
            imagefilledrectangle($image, 5, 5, $props['width']-5, $props['height']-5, $white);

            $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
            if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissingFont'])) {
                $font = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissingFont'];
            }
            if (file_exists($font)) {
                $fontSize = 40;

                $bbox = imagettfbbox($fontSize, 0, $font, $text);
                if($bbox[0] >= -1) {
                    $bbox['x'] = abs($bbox[0] + 1) * -1;
                } else {
                    $bbox['x'] = abs($bbox[0] + 2);
                }
                $bbox['y'] = abs($bbox[5] + 1);
                $bbox['width'] = abs($bbox[2] - $bbox[0]);
                if($bbox[0] < -1) {
                    $bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
                }
                $bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
                if($bbox[3] > 0) {
                    $bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
                }
                imagettftext($image, $fontSize, 0, ($props['width']/2)-($bbox['width']/2), ($props['height']/2)+($bbox['height']/2), 0, $font, $text);
            } else {
                imagestring($image, 5, 50, 50,  $text, $black);
            }
            switch (strtolower($props['extension'])) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $file);
                    break;
                case 'png':
                    imagepng($image, $file);
                    break;
                case 'gif':
                    imagegif($image, $file);
                    break;
                default:
                    touch($file);
                    break;
            }
            imagedestroy($image);
        }
    }
}
