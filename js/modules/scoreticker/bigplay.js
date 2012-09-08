define(function() {

	function BigPlay() {
	}

	BigPlay.prototype = {

		__eid: null,
		__gsis: null,
		__id: null,
		__team: null,
		__message: null,

		get_eid: function()
		{
			return this.__eid;
		},

		set_eid: function(eid)
		{
			this.__eid = eid;
		},

		get_gsis: function()
		{
			return this.__gsis;
		},

		set_gsis: function(gsis)
		{
			this.__gsis = gsis;
		},

		get_id: function()
		{
			return this.__id;
		},

		set_id: function(id)
		{
			this.__id = id;
		},

		get_team: function()
		{
			return this.__team;
		},

		set_team: function(team)
		{
			this.__team = team;
		},

		get_message: function()
		{
			return this.__message;
		},

		set_message: function(msg)
		{
			this.__message = msg;
		},

		set_data: function(data)
		{
			for (var prop in data) {
				if (data.hasOwnProperty(prop)) {
					switch (prop) {
						case 'eid':
							this.set_eid(data.eid);
							break;
						case 'gsis':
							this.set_gsis(data.gsis);
							break;
						case 'id':
							this.set_id(data.id);
							break;
						case 'team':
							this.set_team(data.team);
							break;
						case 'message':
							this.set_message(data.message);
							break;
					}
				}
			}
		}

	};

	return BigPlay;

});
