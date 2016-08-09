'use strict';

var
	_ = require('underscore'),
	ko = require('knockout'),
	$ = require('jquery'),
	Ajax = require('modules/CoreClient/js/Ajax.js'),
	WindowOpener = require('modules/CoreClient/js/WindowOpener.js'),
	UrlUtils = require('modules/CoreClient/js/utils/Url.js'),
	Screens = require('modules/CoreClient/js/Screens.js'),
	
	ModulesManager = require('modules/CoreClient/js/ModulesManager.js'),
	CAbstractSettingsFormView = ModulesManager.run('SettingsWebclient', 'getAbstractSettingsFormViewClass'),
	
	Settings = require('modules/%ModuleName%/js/Settings.js')
;

/**
* @constructor
*/
function CUserSettingsView()
{
	CAbstractSettingsFormView.call(this, Settings.ServerModuleName);
	
	this.connected = ko.observable(Settings.Connected);
	window.facebookSettingsViewModelCallback = _.bind(function (bResult, sMessage) {
		if (!bResult) 
		{
			Screens.showError(sMessage);
		}
		else
		{
			this.connected(true);
		}
	}, this);				
	
}

_.extendOwn(CUserSettingsView.prototype, CAbstractSettingsFormView.prototype);

CUserSettingsView.prototype.ViewTemplate = '%ModuleName%_UserSettingsView';

CUserSettingsView.prototype.connect = function ()
{
	$.cookie('external-services-redirect', 'connect');
	WindowOpener.open(UrlUtils.getAppPath() + '?external-services=facebook', 'Facebook');
};

CUserSettingsView.prototype.disconnect = function ()
{
	Ajax.send(
		Settings.ServerModuleName, 
		'DeleteAccount', 
		null, 
		function (oResponse) {
			if (oResponse.Result)
			{
				this.connected(false);			
			}
			else
			{
				Screens.showError('error');
			}
		}, 
		this
	);
};

module.exports = new CUserSettingsView();
