<?php

namespace SiteMap;

class SiteModel
{
	public $domain;
	public $ftpHost;
	public $ftpPort = 21;
	public $ftpLogin;
	public $ftpPassword;
	public $ftpSiteMapPath;

	public function getSiteUrl()
	{
		return (strpos($this->domain, 'http') === 0 ? '' : 'http://') . $this->domain;
	}

	/**
	 * @param string $domain
	 * @param string $ftpHost
	 * @param string $ftpLogin
	 * @param string $ftpPassword
	 */
	public function __construct($domain, $ftpHost = null, $ftpLogin = null, $ftpPassword = null)
	{
		$this->domain = $domain;
		$this->ftpHost = $ftpHost ? $ftpHost : $domain;
		if ($ftpLogin) $this->ftpLogin = $ftpLogin;
		if ($ftpPassword) $this->ftpPassword = $ftpPassword;
	}

	/**
	 * @param string $message
	 */
	public function error($message)
	{
		error_log(sprintf('[%s] %s', $this->domain, $message));
	}
}
