<?php
/**
 *
 * @package Classes
 * @subpackage AuthIntegrator
 */
class COAuthIntegratorConnectorFacebook extends COAuthIntegratorConnector
{
	public static $ConnectorName = 'facebook';
			
	public function GetSupportedScopes()
	{
		return array('auth', 'filestorage');
	}
	
	public function CreateClient()
	{
		$sRedirectUrl = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ').'/?oauth='.self::$ConnectorName;
		if (!strpos($sRedirectUrl, '://localhost'))
		{
			$sRedirectUrl = str_replace('http:', 'https:', $sRedirectUrl);
		}

		$oClient = new \oauth_client_class;
		$oClient->debug = self::$Debug;
		$oClient->debug_http = self::$Debug;
		$oClient->server = 'Facebook';
		$oClient->redirect_uri = $sRedirectUrl;
		$oClient->client_id = $this->oModule->GetConfig('Id');
		$oClient->client_secret = $this->oModule->GetConfig('Secret');
		$oClient->scope = 'email';
			
		$oOAuthIntegratorWebclientModule = \CApi::GetModule('OAuthIntegratorWebclient');
		if ($oOAuthIntegratorWebclientModule)
		{
			$oClient->configuration_file = $oOAuthIntegratorWebclientModule->GetPath() .'/classes/OAuthClient/'.$oClient->configuration_file;
		}
		
		return $oClient;
	}
	
	public function Init()
	{
		parent::Init();

		$bResult = false;
		$oUser = null;

		$oClient = self::CreateClient();
		if($oClient)
		{
			if(($success = $oClient->Initialize()))
			{
				if(($success = $oClient->Process()))
				{
					if (strlen($oClient->access_token))
					{
						$success = $oClient->CallAPI(
							'https://graph.facebook.com/me',
							'GET',
							array(),
							array('FailOnAccessError' => true),
							$oUser
						);
					}
				}

				$success = $oClient->Finalize($success);
			}

			if($oClient->exit)
			{
				$bResult = false;
				exit;
			}

			if ($success && $oUser)
			{
				$oClient->ResetAccessToken();

				$aSocial = array(
					'type' => self::$ConnectorName,
					'id' => $oUser->id,
					'name' => $oUser->name,
					'email' => isset($oUser->email) ? $oUser->email : '',
					'access_token' => $oClient->access_token,
					'scopes' => self::$Scopes
				);

				\CApi::Log('social_user_' . self::$ConnectorName);
				\CApi::LogObject($oUser);

				$bResult = $aSocial;
			}
			else
			{
				$oClient->ResetAccessToken();
				$bResult = false;
			}
		}
		
		return $bResult;
	}}