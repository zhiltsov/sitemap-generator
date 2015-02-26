<?php
namespace SiteMap;

class Crawler
{
	const DEFAULT_SCHEME = 'http';

	/** @var array */
	private $pages = [];

	/** @var CurlResponse[] */
	private $pageInfo = [];

	/** @var array */
	private $links = [];

	/** @var SiteModel */
	private $siteModel;

	/**
	 * @return array
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * @return CurlResponse[]
	 */
	public function getPageInfo()
	{
		return $this->pageInfo;
	}

	/**
	 * @param SiteModel $o
	 */
	public function __construct(SiteModel $o)
	{
		$this->siteModel = $o;
		$this->links[] = $o->getSiteUrl() . '/';
		while ($this->links) {
			$link = parse_url(array_shift($this->links));
			if (isset($link['host']) && $link['host'] !== $o->domain) continue;

			if (!self::cleanUrl($link)) continue;
			$link = self::buildUrl($link);
			$response = new CurlResponse();
			$response->url = $link;
			if (self::request($link, $response)) {
				$this->pageInfo[] = $response;
				$this->pages[] = $link;

				$match = $match2 = [];
				preg_match_all('#<a(.*)?href="([^":]+)"#', $response->content, $match);
				preg_match_all("#<a(.*)?href='([^':]+)'#", $response->content, $match2);
				if (isset($match[2]) || isset($match2[2])) {
					$match = array_merge($match[2], $match2[2]);
					foreach ($match as $url) {
						$url = parse_url($url);
						if (!isset($url['scheme'])) $url['scheme'] = self::DEFAULT_SCHEME;
						if (!isset($url['host'])) $url['host'] = $o->domain;
						if (!self::cleanUrl($url)) continue;
						$url = self::buildUrl($url);
						if (!in_array($url, $this->pages) && !in_array($url, $this->links)) $this->links[] = $url;
					}
				}
			}
		}
	}

	/**
	 * @param array $url
	 * @return bool
	 */
	public static function cleanUrl(array &$url)
	{
		if (!isset($url['path']) || empty($url['path'])) return true;

		$directories = [];
		$fileName = '';
		$url['path'] = str_replace('/./', '/', $url['path']);
		$url['path'] = ltrim($url['path'], '/#');
		$urlInfo = explode('/', $url['path']);

		foreach ($urlInfo as $i => $item) {
			if (!$item) {
				continue;
			} elseif ($item === '..') {
				if (!$directories) return false;
				array_pop($directories);
				unset($urlInfo[$i]);
			} elseif (isset($urlInfo[$i + 1])) {
				$directories[] = $item;
			} else {
				$fileName = $item;
			}
		}

		$url['path'] = '/' . implode('/', array_merge($directories, [$fileName]));

		return true;
	}

	/**
	 * HEAD запрос
	 * Проверка типа контента без его предварительной загрузки
	 * @param string $url
	 * @param CurlResponse $response
	 * @return bool
	 */
	private static function isRequestValid($url, CurlResponse $response)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true); // HEAD запрос
		$response->parseHeaders(curl_exec($curl));
		$response->statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$response->contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
		curl_close($curl);

		return $response->statusCode === 200 && strripos($response->contentType, 'text/html') !== false;
	}

	/**
	 * @param string $url
	 * @param CurlResponse $response
	 * @return bool
	 */
	private static function request($url, CurlResponse $response)
	{
		if (self::isRequestValid($url, $response)) {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response->content = curl_exec($curl);
			curl_close($curl);
		}

		return (bool)$response->content;
	}

	/**
	 * @param array $url
	 * @return string
	 */
	private static function buildUrl(array $url)
	{
		return sprintf(
			'%s://%s%s%s',
			$url['scheme'],
			$url['host'],
			isset($url['path']) ? $url['path'] : '/',
			isset($url['query']) ? '?' . $url['query'] : ''
		);
	}
}
