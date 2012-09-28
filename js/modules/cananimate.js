define(['cs!./getieversion'], function(ieversion) {
	return (ieversion === -1) || (ieversion > 8);
});
