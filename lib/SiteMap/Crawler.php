<?php
namespace SiteMap;

class Сrawler
{
	private $pages;
	private $links;
	private $siteModel;

	const DEFAULT_SCHEME = 'http';

	public function getPages()
	{
		return $this->pages;
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
			if ($data = self::request($link)) {
				$this->pages[] = $link;
				$match = $match2 = [];
				preg_match_all('#<a(.*)?href="([^":]+)"#', $data, $match);
				preg_match_all("#<a(.*)?href='([^':]+)'#", $data, $match2);
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
	 * @return bool
	 */
	private static function isRequestValid($url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true); // HEAD запрос
		curl_exec($curl);
		$statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
		curl_close($curl);

		return $statusCode === 200 && strripos($contentType, 'text/html') !== false;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	private static function request($url)
	{
		$response = null;
		if (self::isRequestValid($url)) {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			curl_close($curl);
		}

		return $response;
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
