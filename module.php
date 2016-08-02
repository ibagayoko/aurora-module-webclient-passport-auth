<?php

class FacebookAuthModule extends AApiModule
{
	protected $sService = 'facebook';
	
	protected $aSettingsMap = array(
		'Id' => array('', 'string'),
		'Secret' => array('', 'string')
	);
	
	protected $aRequireModules = array(
		'ExternalServices'
	);
	
	public function init() 
	{
		$this->incClass('connector');
		$this->subscribeEvent('ExternalServicesAction', array($this, 'onExternalServicesAction'));
		$this->subscribeEvent('GetServices', array($this, 'onGetServices'));
		$this->subscribeEvent('GetServicesSettings', array($this, 'onGetServicesSettings'));
		$this->subscribeEvent('UpdateServicesSettings', array($this, 'onUpdateServicesSettings'));
}
	
	/**
	 * Adds service name to array passed by reference.
	 * 
	 * @param array $aServices Array with services names passed by reference.
	 */
	public function onGetServices(&$aServices)
	{
		$aServices[] = $this->sService;
	}
	
	/**
	 * Adds service settings to array passed by reference.
	 * 
	 * @param array $aServices Array with services settings passed by reference.
	 */
	public function onGetServicesSettings(&$aServices)
	{
		$aServices[] = array(
			'Name' => $this->sService,
			'DisplayName' => $this->GetName(),
			'EnableModule' => $this->getConfig('EnableModule', false),
			'Id' => $this->getConfig('Id', ''),
			'Secret' => $this->getConfig('Secret', '')
		);
	}
	
	/**
	 * Updates service settings.
	 * 
	 * @param array $aServices Array with new values for service settings.
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function onUpdateServicesSettings($aServices)
	{
		try
		{
			$aSettings = $aServices[$this->sService];
			$this->setConfig('EnableModule', $aSettings['EnableModule']);
			$this->setConfig('Id', $aSettings['Id']);
			$this->setConfig('Secret', $aSettings['Secret']);
			$this->saveModuleConfig();
		}
		catch (Exception $ex)
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::CanNotSaveSettings);
		}
	}	
	
	public function onExternalServicesAction($sService, &$mResult)
	{
		if ($sService === $this->sService)
		{
			$mResult = false;
			$oConnector = new CExternalServicesConnectorFacebook($this);
			if ($oConnector)
			{
				$mResult = $oConnector->Init();
			}
		}
	}
}
