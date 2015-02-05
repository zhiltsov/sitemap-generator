<?php
namespace SiteMap;

class FtpManager
{
	const FTP_TIMEOUT = 5;

	/** @var SiteModel */
	private $resource;

	/** @var resource */
	private $connect;

	/** @var bool */
	public $error = false;

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

	/**
	 * @return bool
	 */
	public function login()
	{
		if (!@ftp_login($this->connect, $this->resource->ftpLogin, $this->resource->ftpPassword)) {
			$this->error('Авторизация не пройдена');
			return false;
		}

		return true;
	}

	/**
	 * @param resource $file
	 * @return bool
	 */
	public function saveSiteMapFile($file)
	{
		if (!ftp_fput($this->connect, $this->resource->ftpSiteMapPath, $file, FTP_ASCII)) {
			$this->error('Невозможно загрузить Sitemap');

			return false;
		}

		return true;
	}

	/**
	 * @param string $message
	 */
	public function error($message)
	{
		$this->error = true;
		$this->resource->error($message);
	}
}
