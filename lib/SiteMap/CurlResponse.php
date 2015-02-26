<?php

namespace SiteMap;

class CurlResponse
{
	/** @var string */
	public $url;

	/** @var int */
	public $statusCode;

	/** @var string */
	public $contentType;

	/** @var int timestamp */
	public $lastModified;

	/** @var string */
	public $content;

	/**
	 * @param string $data
	 */
	public function parseHeaders($data)
	{
		foreach (explode(PHP_EOL, $data) as $row) {
			if (count($value = explode(': ', $row)) === 1) continue;
			if (strtolower($value[0]) === 'last-modified') {
				$this->lastModified = strtotime($value[1]);
			}
		}
	}
}
