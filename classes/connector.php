<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

/**
 *
 * @package Classes
 * @subpackage AuthIntegrator
 */
class COAuthIntegratorConnectorFacebook extends COAuthIntegratorConnector
{
	protected $Name = 'facebook';
	
	public function CreateClient($sId, $sSecret, $sScope)
	{
		$sRedirectUrl = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ').'/?oauth='.$this->Name;
		if (!strpos($sRedirectUrl, '://localhost'))
		{
			$sRedirectUrl = str_replace('http:', 'https:', $sRedirectUrl);
		}

		$oClient = new \oauth_client_class;
		$oClient->debug = self::$Debug;
		$oClient->debug_http = self::$Debug;
		$oClient->server = 'Facebook';
		$oClient->redirect_uri = $sRedirectUrl;
		$oClient->client_id = $sId;
		$oClient->client_secret = $sSecret;
		$oClient->scope = 'email';
			
		$oOAuthIntegratorWebclientModule = \Aurora\System\Api::GetModule('OAuthIntegratorWebclient');
		if ($oOAuthIntegratorWebclientModule)
		{
			$oClient->configuration_file = $oOAuthIntegratorWebclientModule->GetPath() .'/classes/OAuthClient/'.$oClient->configuration_file;
		}
		
		return $oClient;
	}
	
	public function Init($sId, $sSecret, $sScope = '')
	{
		$mResult = false;

		$oClient = $this->CreateClient($sId, $sSecret, $sScope);
		if($oClient)
		{
			$oUser = null;
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
							array(
								'FailOnAccessError' => true
							),
							$oUser
						);
					}
				}

				$success = $oClient->Finalize($success);
			}

			if($oClient->exit)
			{
				exit;
			}

			if ($success && $oUser)
			{
				$mResult = array(
					'type' => $this->Name,
					'id' => $oUser->id,
					'name' => $oUser->name,
					'email' => isset($oUser->email) ? $oUser->email : '',
					'access_token' => $oClient->access_token,
					'scopes' => explode('|', $sScope)
				);
			}
			else
			{
				$oClient->ResetAccessToken();
				$mResult = false;
			}
		}
		
		return $mResult;
	}}