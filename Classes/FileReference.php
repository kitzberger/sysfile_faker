<?php
namespace Kitzberger\SysfileFaker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileReference extends \TYPO3\CMS\Core\Resource\FileReference
{
	/**
	 * @var string
	 */
	private $publicUrl;

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

		$this->determinePublicUrl();

		if (empty($this->publicUrl)) {
			// \TYPO3\CMS\Core\Utility\DebugUtility::debug('Abort sysfile_faker, due to empty publicUrl. Folder missing?');
			return;
		}

		$file = PATH_site . $this->publicUrl;

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

	public function determinePublicUrl()
	{
		$fileExtension = $this->getExtension();

		$this->isRemoteVideoPlaceholder = in_array($this->getExtension(), ['youtube', 'vimeo']);

		if ($this->isRemoteVideoPlaceholder) {
			// .youtube and .video
			$this->publicUrl = $this->getStorage()->getPublicUrl($this);
		} else {
			// .pdf, .jpg, .png, etc.
			$this->publicUrl = $this->getPublicUrl();
		}
	}

	private function ensureFolderExists()
	{
		// create folder if necessary
		$folder = dirname(PATH_site . $this->publicUrl);
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

		$remoteFile = rtrim($host, '/') . '/' . $this->publicUrl;

		$headers = ["Authorization: Basic " . base64_encode($credentials)];

		$report = [];
		$content = GeneralUtility::getUrl($remoteFile.'2', 0, $headers, $report);
		if ($content) {
			$localFile = PATH_site . $this->publicUrl;
			GeneralUtility::writeFile($localFile, $content);
		}
	}

	protected function createFakeFile()
	{
		// create folder if necessary
		$this->ensureFolderExists();

		$file = PATH_site . $this->publicUrl;

		//var_dump('Faking: ' . PATH_site . $this->publicUrl);
		//var_dump($this->getProperties());

		if ($this->isRemoteVideoPlaceholder) {
			GeneralUtility::writeFile($file, 'DLzxrzFCyOs');
			return;
		}

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

	/**
	 * Workaround for clickenlarge rendering
	 */
	public function __toString() {
		return '';
	}
}
