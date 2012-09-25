(function() {

	var width = 900;
	var height = 950;
	var headerheight = 50;

	var graphContainer = null;
	var graphControlsContainer = null;

	var userselect = null;

	var svg = null;
	var xaxisgroup = null;
	var yaxisgroup = null;

	var usergroup = null;
	var gamegroup = null;

	var headergroup = null;
	var headertext1 = null;
	var headertext2 = null;

	var loadingtext = null;

	var midline = null;
	var weeklabel = null;
	var favoritelabel = null;
	var underdoglabel = null;

	var yaxis = null;
	var xscale = null;
	var yscale = null;

	var color = null;
	var line = null;

	var pooldata = null;

	var activedata = null;

	function readable_name(user)
	{
		var name = user.username;
		if (user.first_name) {
			name = user.first_name;
			if (user.last_name) {
				name += ' ' + user.last_name;
			}
		}
		return name;
	}

	function set_user(username)
	{
		if (username) {

			var userdata = null;
			for (var i = 0; i < activedata.length; i++) {
				if (activedata[i].user.username == username) {
					userdata = activedata[i];
					break;
				}
			}

			if (userdata) {
			
				headertext1
					.text(readable_name(userdata.user))
					.transition()
					.duration(500)
					.style('opacity', 1);

				if (userdata.hasOwnProperty('averagespread')) {
					headertext2
						.text('Average pick spread: ' + userdata.averagespread)
						.transition()
						.duration(500)
						.style('opacity', 1);
				}

				svg.selectAll('g.user path')
					.filter(function (d2, i2) {
						return d2.user.username != username;
					})
					.transition()
					.duration(500)
					.style('opacity', .1);

				gamegroup.selectAll('circle')
					.each(function (cd, ci) {
						for (var j = 0; j < userdata.pickdata.length; j++) {
							var pick = userdata.pickdata[j];
							if (pick.game == cd.key) {
								d3.select(this)
									.attr('cy', yscale(pick.spread))
									.attr('fill', function() {
										if (pick.win > 0)
											return 'blue';
										if (pick.win < 0)
											return 'red';
										return 'black';
									})
									.transition()
									.duration(500)
									.attr('r', 4)
									.style('opacity', 1);
								return;
							}
						}
					});

			}

			return;

		}


		headertext1
			.transition()
			.duration(500)
			.style('opacity', 0);

		headertext2
			.transition()
			.duration(500)
			.style('opacity', 0);

		svg.selectAll('g.user path')
			.transition()
			.duration(500)
			.style('opacity', 1);

		gamegroup.selectAll('circle')
			.transition()
			.duration(500)
			.style('opacity', 0)
			.attr('r', 0)
			.attr('fill', 'black');

	}

	function set_pool(pool)
	{
		if (!pool)
			return;

		var data = pooldata[pool];

		xscale.domain([1, data.weeks]);

		var upperspread = 0;
		var lowerspread = 0;
		for (var user in data.entries) {
			if (!data.entries.hasOwnProperty(user))
				continue;

			for (var i = 0; i < data.entries[user].picks.length; i++) {
				var pick = data.entries[user].picks[i];
				var game = data.games[pick.game];
				if (game.point_spread) {
					if (game.point_spread > upperspread) {
						upperspread = game.point_spread;
						lowerspread = game.point_spread * -1;
					}
				}
			}

		}

		yscale.domain([upperspread, lowerspread]);

		var users = [];

		activedata = [];
		for (var i = 0; i < data.entries.length; i++) {
			
			var entrant = data.entries[i];

			var dataset = [];

			var averagespread = 0;
			var spreadcount = 0;

			for (var j = 0; j < entrant.picks.length; j++) {
				var pick = entrant.picks[j];
				var game = data.games[pick.game];
				var datapoint = null;

				var win = 0;
				var spread = 0;

				if (game.point_spread && game.favorite && pick.team) {

					if (
						((pick.team == game.home_team) && (game.home_score > game.away_score)) ||
						((pick.team == game.away_team) && (game.away_score > game.home_score))
					) {
						win = 1;
					} else if (
						((pick.team == game.home_team) && (game.home_score < game.away_score)) ||
						((pick.team == game.away_team) && (game.away_score < game.home_score))
					) {
						win = -1;
					}

					spread = game.point_spread;
					if (pick.team != game.favorite)
						spread *= -1;

					averagespread += spread;
					spreadcount++;
				}

				datapoint = { week: pick.week, spread: spread, win: win, game: pick.game };

				dataset.push(datapoint);
				dataset.sort(function(a, b) {
					if (a.week < b.week)
						return -1;
					if (a.week > b.week)
						return 1;
					return 0;
				});
			}

			var entrantdata = {
				user: entrant.user,
				pickdata: dataset
			};
			if (spreadcount > 0) {
				entrantdata.averagespread = Math.round((averagespread / spreadcount)*10)/10;
			}

			activedata.push(entrantdata);

			users.push(entrant.user);
		}

		
		// user selector
		userselect.selectAll('option').remove();
		userselect.append('option')
			.attr('value', '')
			.text('All users');
		users.sort(function(a, b) {
			var an = readable_name(a).toLowerCase();
			var bn = readable_name(b).toLowerCase();
			if (an < bn)
				return -1;
			if (an > bn)
				return 1;
			return 0;
		});
		for (var i = 0; i < users.length; i++) {
			userselect.append('option')
				.attr('value', users[i].username)
				.text(readable_name(users[i]));
		}


		// user data binding
		var maindata = usergroup.selectAll('g.user').data(activedata, function(d) { return d.user.username; });
		maindata.exit()
			.transition()
			.duration(500)
			.style('opacity', 0)
			.remove();

		var newdatagroup = maindata.enter().append('g')
			.attr('class', 'user')
			.style('opacity', 1);

		// circle drawing
		var gameentries = d3.entries(data.games);
		var gamedata = gamegroup.selectAll('circle').data(gameentries, function(d) { return d.key; });

		gamedata.exit().remove();

		gamedata
			.attr('cx', function(d) {
				return xscale(d.value.week);
			});

		gamedata.enter().append('circle')
			.attr('cx', function(d) {
			 	return xscale(d.value.week);
			})
			.attr('r', 0)
			.attr('fill', 'black')
			.style('opacity', 0);


		// line drawing

		maindata.select('path')
			.transition()
			.duration(500)
			.attr('d', function(d) {
				return line(d.pickdata);
			})
			.style('stroke', function (d, i) { return color(i); });
		newdatagroup.append('path')
			.attr('d', function(d) {
				return line(d.pickdata);
			})
			.style('fill', 'none')
			.style('stroke', function(d, i) { return color(i); })
			.style('stroke-width', 3)
			.style('opacity', 0)
			.on('mouseover', function(d, i) {
				var option = userselect.node().options[userselect.node().selectedIndex];
				if (!option.value) {
					set_user(d.user.username);
				}
			})
			.on('mouseout', function(d, i) {
				var option = userselect.node().options[userselect.node().selectedIndex];
				if (!option.value) {
					set_user(null);
				}
			})
			.transition()
			.duration(500)
			.style('opacity', 1);

		yaxis.scale(yscale);

		yaxisgroup
			.transition()
			.duration(500)
			.call(yaxis);

		var xaxisdata = xaxisgroup.selectAll('g')
			.data(xscale.ticks(data.weeks));

		var xaxisdatagroup = xaxisdata.enter().append('g');

		xaxisdatagroup.append('line')
			.attr('x1', xscale)
			.attr('x2', xscale)
			.attr('y1', (40+headerheight))
			.attr('y2', (height-50))
			.style('stroke', '#ccc');
		xaxisdatagroup.append('text')
			.attr('x', xscale)
			.attr('y', (height-30))
			.attr('text-anchor', 'middle')
			.text(function (d) { return d; });

		if (!midline) {
			midline = svg.append('line')
				.style('stroke', '#ccc');
		}
		midline
			.attr('x1', 40)
			.attr('x2', (width-20))
			.attr('y1', yscale(0))
			.attr('y2', yscale(0));
	}

	function initialize(container, controlscontainer)
	{
		graphContainer = container;
		graphControlsContainer = controlscontainer;

		svg = d3.select(graphContainer)
			.append('svg')
			.attr('width', width)
			.attr('height', height);

		color = d3.scale.category20b();

		xscale = d3.scale.linear()
			.range([50, (width-20)]);

		yscale = d3.scale.linear()
			.range([(40+headerheight), (height-30)]);

		yaxis = d3.svg.axis()
			.orient('right');

		xaxisgroup = svg.append('g');
		yaxisgroup = svg.append('g');

		usergroup = svg.append('g');
		gamegroup = svg.append('g');

		headergroup = svg.append('g');
		headertext1 = headergroup.append('text')
			.attr('x', 0)
			.attr('y', 20)
			.attr('font-size', 24)
			.style('opacity', 0);
		headertext2 = headergroup.append('text')
			.attr('x', 0)
			.attr('y', 40)
			.attr('font-size', 20)
			.style('opacity', 0);

		loadingtext = svg.append('text')
			.attr('x', width/2)
			.attr('y', height/2)
			.attr('font-size', 20)
			.attr('text-anchor', 'middle')
			.style('opacity', 1)
			.text('Loading...');

		line = d3.svg.line()
			.x(function(d) { 
				return xscale(d.week);
			})
			.y(function(d) { 
				return yscale(d.spread);
			})
			.interpolate('cardinal')
			.tension(0.9);

		d3.json('index.php?a=graphdata&g=pickrisk', function(data) {
			pooldata = data;

			loadingtext.transition()
				.duration(500)
				.style('opacity', 0)
				.remove();

			var controls = d3.select(graphControlsContainer);
			var poolselect = controls.append('select')
				.style('font-size', 'larger');
			var firstpool = null;
			for (var pool in data) {
				if (data.hasOwnProperty(pool)) {
					poolselect.append('option')
						.attr('value', pool)
						.text(data[pool].name + ' [' + data[pool].season + '-' + (data[pool].season+1) + ']');
					if (!firstpool)
						firstpool = pool;
				}
			}
			poolselect.on('change', function(d) {
				var select = d3.select(this);
				var option = select.node().options[select.node().selectedIndex];
				var selectedpool = option.value;

				var selecteduser = userselect.node().options[userselect.node().selectedIndex];
				if (selecteduser.value) {
					set_user(null);
					setTimeout(function() {set_pool(selectedpool);}, 500);
				} else {
					set_pool(selectedpool);
				}
			});

			controls.append('br');

			userselect = controls.append('select')
				.style('font-size', 'larger');
			userselect.on('change', function(d) {
				var select = d3.select(this);
				var option = select.node().options[select.node().selectedIndex];
				set_user(null);
				if (option.value) {
					set_user(option.value);
				}
			});

			weeklabel = svg.append('text')
				.attr('x', width/2)
				.attr('y', height-10)
				.attr('text-anchor', 'middle')
				.text('Week');

			favoritelabel = svg.append('text')
				.attr('x', 0)
				.attr('y', (30+headerheight))
				.text('Favorite');

			underdoglabel = svg.append('text')
				.attr('x', 0)
				.attr('y', height-10)
				.text('Underdog');

			set_pool(firstpool);
		});
	}

	$(document).ready(function() {
		if (Modernizr.inlinesvg && d3) {
			initialize('#graph', '#graphControls');
		} else {
			$('div.navTabs').remove();

			var graph = $('#graph');

			var line1 = $(document.createElement('div'))
				.text('Analytics requires features that your browser does not support.');
			graph.append(line1);
			
			var line2 = $(document.createElement('div'))
				.css('padding-bottom', '10px')
				.text('Please upgrade to one of the following browsers:');
			graph.append(line2);

			var chrome = $(document.createElement('div'));
			var chromelink = $(document.createElement('a'))
				.attr('href', 'http://www.google.com/chrome')
				.attr('target', '_blank')
				.text('Google Chrome');
			chrome.append(chromelink);
			graph.append(chrome);

			var firefox = $(document.createElement('div'));
			var firefoxlink = $(document.createElement('a'))
				.attr('href', 'http://www.getfirefox.com')
				.attr('target', '_blank')
				.text('Mozilla Firefox');
			firefox.append(firefoxlink);
			graph.append(firefox);

			var ie9 = $(document.createElement('div'));
			var ie9link = $(document.createElement('a'))
				.attr('href', 'http://windows.microsoft.com/en-us/internet-explorer/products/ie/home')
				.attr('target', '_blank')
				.text('Internet Explorer 9');
			ie9.append(ie9link);
			graph.append(ie9);
		}
	});

})()
