<?php
namespace SiteMap;

class SiteManager
{
	/** @var string */
	private static $iniFile;

	/** @var SiteModel[] */
	private static $sites = [];

	/**
	 * @param string $iniFile
	 * @throws \Exception
	 */
	public static function loadConfig($iniFile)
	{
		self::$iniFile = $iniFile;

		foreach (parse_ini_file($iniFile, true) as $domain => $data) {
			if (!isset($data['ftp_login']) || !isset($data['ftp_password']) || !isset($data['ftp_sitemap_path'])) {
				throw new \Exception('Не заданы обязательные поля');
			}

			$o = new SiteModel($domain);
			if (isset($data['ftp_host'])) $o->ftpHost = $data['ftp_host'];
			if (isset($data['ftp_port'])) $o->ftpPort = $data['ftp_port'];
			$o->ftpLogin = $data['ftp_login'];
			$o->ftpPassword = $data['ftp_password'];
			$o->ftpSiteMapPath = $data['ftp_sitemap_path'];

			self::addSite($o);
		}
	}

	/**
	 * @param SiteModel $o
	 */
	public static function addSite(SiteModel $o)
	{
		self::$sites[] = $o;
	}

	public static function run()
	{
		foreach (self::$sites as $site) {
			$scanner = new Crawler($site);
			$generator = new Generator();

			if ($scanner->getPages()) {
				foreach ($scanner->getPages() as $url) {
					$generator->addUrl($url, date('Y-m-d'));
				};
				$generator->save();

				$ftp = new FtpManager($site);
				!$ftp->error && $ftp->login() && $ftp->saveSiteMapFile($generator->save());
			} else {
				error_log(sprintf('[%s] Не удалось установить соединение', $site->domain));
			}
		}
	}
}
