'use strict';

module.exports = {
	ServerModuleName: 'FacebookAuth',
	HashModuleName: 'facebook-auth',
	
	Connected: false,
	
	/**
	 * Initializes settings from AppData object section of this module.
	 * 
	 * @param {Object} oAppDataSection Object contained module settings.
	 */
	init: function (oAppDataSection)
	{
		if (oAppDataSection)
		{
			this.Connected = !!oAppDataSection.Connected;
		}
	}
};
