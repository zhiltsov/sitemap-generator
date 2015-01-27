<?php
namespace SiteMap;

class FtpManager
{
	const FTP_TIMEOUT = 5;

	/** @var SiteModel */
	private $resource;

	/** @var resource */
	private $connect;

	/**
	 * @param SiteModel $o
	 */
	public function __construct(SiteModel $o)
	{
		$this->resource = $o;
		if (!$this->connect = ftp_connect($o->ftpHost, $o->ftpPort, self::FTP_TIMEOUT)) {
			$this->error('Не удалось установить соединение');
		}
	}

	public function __destruct()
	{
		ftp_close($this->connect);
	}

	public function login()
	{
		if (!@ftp_login($this->connect, $this->resource->ftpLogin, $this->resource->ftpPassword)) {
			$this->error('Авторизация не пройдена');
		}
	}

	public function saveSiteMapFile($file)
	{
		if (!ftp_fput($this->connect, $this->resource->ftpSiteMapPath, $file, FTP_ASCII)) {
			$this->error('Невозможно загрузить Sitemap');
		}
	}

	/**
	 * @param string $message
	 */
	public function error($message)
	{
		$this->resource->error($message);
	}
}
