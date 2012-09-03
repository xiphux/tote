define(['modules/getieversion'], function(ieversion) {
	return (ieversion === -1) || (ieversion > 8);
});
