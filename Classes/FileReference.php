<?php

namespace Kitzberger\SysfileFaker;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;

class FileReference extends \TYPO3\CMS\Core\Resource\FileReference
{
    /**
     * @var string
     */
    private $relativePublicUrl;

    /**
     * @var  boolean
     */
    private $isRemoteVideoPlaceholder = false;

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

        $this->determineRelativeUrl();
        if (empty($this->relativePublicUrl)) {
            // \TYPO3\CMS\Core\Utility\DebugUtility::debug('Abort sysfile_faker, due to empty relativePublicUrl. Folder missing?');
            return;
        }

        $file = Environment::getPublicPath() . '/' . $this->relativePublicUrl;

        if (!file_exists($file)) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['loadFilesFromRemoteIfMissing']) &&
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['loadFilesFromRemoteIfMissing']) {
                $this->loadFileFromRemote();
            }
        }

        if (!file_exists($file)) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissing']) &&
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissing']) {
                $this->createFakeFile();
            }
        }
    }

    public function determineRelativeUrl()
    {
        $this->isRemoteVideoPlaceholder = in_array($this->getExtension(), ['youtube', 'vimeo']);

        if ($this->isRemoteVideoPlaceholder) {
            // .youtube and .video
            $this->relativePublicUrl = $this->getStorage()->getPublicUrl($this);
        } else {
            // .pdf, .jpg, .png, etc.
            $this->relativePublicUrl = $this->getPublicUrl();
        }

        // EXT:headless compatibility
        if ($parts = parse_url($this->relativePublicUrl)) {
            $this->relativePublicUrl = $parts['path'];
        }
    }

    private function ensureFolderExists()
    {
        // create folder if necessary
        $folder = dirname(Environment::getPublicPath() . '/' . $this->relativePublicUrl);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
    }

    protected function loadFileFromRemote()
    {
        // create folder if necessary
        $this->ensureFolderExists();

        $host = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['loadFilesFromRemoteIfMissing']['remoteHost'];
        $credentials = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['loadFilesFromRemoteIfMissing']['remoteHostBasicAuth'];

        $remoteFile = rtrim($host, '/') . '/' . $this->relativePublicUrl;

        $headers = [];

        if ($credentials) {
            $headers[] = 'Authorization: Basic ' . base64_encode($credentials);
        }

        $report = [];
        $content = GeneralUtility::getUrl($remoteFile, 0, $headers, $report);
        if ($content) {
            $localFile = Environment::getPublicPath() . '/' . $this->relativePublicUrl;
            GeneralUtility::writeFile($localFile, $content);
        }
    }

    protected function createFakeFile()
    {
        // create folder if necessary
        $this->ensureFolderExists();

        $file = Environment::getPublicPath() . '/' . $this->relativePublicUrl;

        //var_dump('Faking: ' . Environment::getPublicPath() . '/' . $this->relativePublicUrl);
        //var_dump($this->getProperties());

        switch ($this->getExtension()) {
            case 'youtube':
                $this->createFakeYoutubeFile($file);
                break;
            case 'vimeo':
                $this->createFakeVimeoFile($file);
                break;
            case 'pdf':
                $this->createFakePdfFile($file);
                break;
            case 'txt':
                $this->createFakeTxtFile($file);
                break;
            default:
                $this->createFakeImageFile($file);
                break;
        }
    }

    protected function createFakeYoutubeFile($file, $content = 'DLzxrzFCyOs')
    {
        GeneralUtility::writeFile($file, $content);
    }

    protected function createFakeVimeoFile($file, $content = '148751763')
    {
        GeneralUtility::writeFile($file, $content);
    }

    protected function createFakeTxtFile($file, $content = 'This is a dummy TXT file.')
    {
        GeneralUtility::writeFile($file, $content);
    }

    protected function createFakePdfFile($file, $dummy = 'EXT:sysfile_faker/Resources/Private/Dummy/dummy.pdf')
    {
        $dummy = GeneralUtility::getFileAbsFileName($dummy);
        $dummy = GeneralUtility::fixWindowsFilePath($dummy);

        GeneralUtility::upload_copy_move($dummy, $file);
    }

    protected function createFakeImageFile($file)
    {
        $props = $this->getProperties();

        if ((int)$props['width'] * (int)$props['height'] === 0) {
            // \TYPO3\CMS\Core\Utility\DebugUtility::debug('Abort sysfile_faker, due to image having no width or height.');
            return;
        }

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
            if ($bbox[0] >= -1) {
                $bbox['x'] = abs($bbox[0] + 1) * -1;
            } else {
                $bbox['x'] = abs($bbox[0] + 2);
            }
            $bbox['y'] = abs($bbox[5] + 1);
            $bbox['width'] = abs($bbox[2] - $bbox[0]);
            if ($bbox[0] < -1) {
                $bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
            }
            $bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
            if ($bbox[3] > 0) {
                $bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
            }
            imagettftext($image, $fontSize, 0, ($props['width']/2)-($bbox['width']/2), ($props['height']/2)+($bbox['height']/2), 0, $font, $text);
        } else {
            imagestring($image, 5, 50, 50, $text, $black);
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

    /**
     * Workaround for clickenlarge rendering
     */
    public function __toString()
    {
        return '';
    }
}
