<?php
namespace SiteMap;

class Generator
{
	/** @var \DOMDocument */
	private $xml;

	/** @var \DOMNode */
	private $xmlUrlSet;

	/** @var resource */
	private $tmpFile;

	const FILE_NAME = 'sitemap.xml';

	public function __construct()
	{
		$this->xml = new \DOMDocument('1.0', 'utf-8');
		$this->xml->formatOutput = true;

		/** <urlset> */
		$url_set_element = $this->xml->createElement('urlset');
		$url_set_element_xmlns = $this->xml->createAttribute('xmlns');
		$url_set_element_xmlns->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
		$url_set_element->appendChild($url_set_element_xmlns);
		$this->xmlUrlSet = $this->xml->appendChild($url_set_element);
	}

	/**
	 * @param string $url
	 * @param string $lastMod
	 */
	public function addUrl($url, $lastMod = null)
	{
		/** <urlset><url> */
		$url_element = $this->xml->createElement('url');
		$urlNode = $this->xmlUrlSet->appendChild($url_element);

		/** <urlset><url><loc> */
		$loc_element = $this->xml->createElement('loc');
		$loc_element_text = $this->xml->createTextNode($url);
		$loc_element->appendChild($loc_element_text);
		$urlNode->appendChild($loc_element);

		if ($lastMod) {
			/** <urlset><url><lastmod> */
			$lastMod_element = $this->xml->createElement('lastmod');
			$lastMod_element_text = $this->xml->createTextNode($lastMod);
			$lastMod_element->appendChild($lastMod_element_text);
			$urlNode->appendChild($lastMod_element);
		}
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->xml->saveXML();
	}

	/**
	 * @return resource
	 */
	public function save()
	{
		$filePath = self::getTempFilePath();
		$this->xml->save($filePath);
		$this->tmpFile = fopen($filePath, 'r+');

		return $this->tmpFile;
	}

	/**
	 * @return string
	 */
	public static function getTempFilePath()
	{
		return sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::FILE_NAME;
	}

	public function __destruct()
	{
		if ($this->tmpFile) fclose($this->tmpFile);
		unlink(self::getTempFilePath());
	}
}
