(function() {

		var graphContainer = null;
		var graphControlsContainer = null;

		var color = null;
		var pie = null;
		var arc = null;
		var grownArc = null;

		var svg = null;
		var placeholderGroup = null;
		var arcGroup = null;
		var centerGroup = null;
		var teamLabel = null;
		var countLabel = null;
		var picksLabel = null;

		var pickData = null;


		function pieTween(d)
		{
			var start = {};
			if (this._current) {
				start.startAngle = this._current.startAngle;
				start.endAngle = this._current.endAngle;
			} else {
				start.startAngle = 0;
				start.endAngle = 0;
			}
			var i = d3.interpolate(start, {startAngle: d.startAngle, endAngle: d.endAngle});
			this._current = i(0);
			return function(t) {
				return arc(i(t));
			};
		}

		function removePieTween(d)
		{
			var i = d3.interpolate({startAngle: d.startAngle, endAngle: d.endAngle}, {startAngle: 0, endAngle: 0});
			this._current = i(0);
			return function(t) {
				return arc(i(t));
			};
		}

		function combinedData()
		{
			var combinedData = {};

			for (var pool in pickData) {
				if (!pickData.hasOwnProperty(pool))
					continue;

				if (!pickData[pool].picks)
					continue;

				var picks = pickData[pool].picks;

				for (var team in picks) {
					if (!picks.hasOwnProperty(team))
						continue;
					if (!combinedData[team]) {
						combinedData[team] = picks[team];
					} else {
						combinedData[team] += picks[team];
					}
				}
			}

			return combinedData;
		}

		function set_pool(pool)
		{
			var data = null;
			if (!pool) {
				data = combinedData();
			} else {
				data = pickData[pool].picks;
			}

			var dataEntries = d3.entries(data);
			var count = 0;
			if (dataEntries.length > 0) {
				dataEntries.forEach(function(d) {
					count += d.value;
				});
			}
			countLabel.text(count);
			if (count == 1) {
				picksLabel.text('pick');
			} else {
				picksLabel.text('picks');
			}

			var paths = arcGroup.selectAll("path").data(pie(dataEntries), function(d) {
				return d.key;
			});

			paths.enter().append("path")
				.attr("fill", function(d, i) { return color(i); })
				.transition()
				.duration(750)
				.attrTween("d", pieTween)
				.each("end", function(d) {
					if (placeholderGroup) {
						placeholderGroup.remove();
						placeholderGroup = null;
					}
				});

			paths
				.transition()
				.duration(750)
				.attrTween("d", pieTween);

			paths.exit()
				.transition()
				.duration(750)
				.attrTween("d", removePieTween)
				.remove();

			arcGroup.selectAll("path").on("mouseover", function(d) {
					d3.select(this).transition()
						.duration(250)
						.attr("d", grownArc);
					teamLabel.transition().style('opacity', 1).text(d.data.key);
					countLabel.text(d.data.value);
					if (d.data.value == 1) {
						picksLabel.text('pick');
					} else {
						picksLabel.text('picks');
					}
				})
				.on("mouseout", function(d) {
					d3.select(this).transition()
						.duration(250)
						.attr("d", arc);
					teamLabel.transition().style('opacity', 0);
					countLabel.text(count);
					if (count == 1) {
						picksLabel.text('pick');
					} else {
						picksLabel.text('picks');
					}
				});
		}

		function initialize(graphcontainer, graphcontrolscontainer)
		{
			graphContainer = graphcontainer;
			graphControlsContainer = graphcontrolscontainer;

			pie = d3.layout.pie().value(function(d) {
				return d.value;
			});

			color = d3.scale.category20();

			svg = d3.select(graphContainer).append("svg")
				.attr('width', 500)
				.attr('height', 500);

			placeholderGroup = svg.append("g")
				.attr('transform', 'translate(250,250)');

			arcGroup = svg.append("g")
				.attr('transform', 'translate(250,250)');

			centerGroup = svg.append("g")
				.attr("transform", "translate(250,250)");

			teamLabel = centerGroup.append("text")
				.attr("dy", -25)
				.attr("font-size", "20")
				.attr("text-anchor", "middle")
				.style('opacity', 0);

			countLabel = centerGroup.append("text")
				.attr("dy", 0)
				.attr("text-anchor", "middle")
				.attr("font-size", "20")
				.text("Loading");

			picksLabel = centerGroup.append("text")
				.attr("dy", 20)
				.attr("text-anchor", "middle")
				.attr("fill", "gray")
				.attr("font-size", "16")
				.text("picks");

			placeholderGroup.append("path")
				.attr("fill", "#EFEFEF")
				.attr("d", d3.svg.arc().innerRadius(100).outerRadius(200).startAngle(0).endAngle(6.28318531)());

			arc = d3.svg.arc().innerRadius(100).outerRadius(200);
			grownArc = d3.svg.arc().innerRadius(100).outerRadius(220);

			d3.json('index.php?a=graphdata&g=pickdist', function(data) {
				pickData = data;

				var controlsContainer = d3.select(graphControlsContainer);
				var poolselect = controlsContainer.append('select')
					.style('font-size', 'larger');
				poolselect.append('option').attr('value', '').text('All pools');
				for (var pool in data) {
					if (data.hasOwnProperty(pool)) {
						poolselect.append('option')
							.attr('value', pool)
							.text(data[pool].name + ' [' + data[pool].season + '-' + (data[pool].season+1) + ']');
					}
				}
				poolselect.on('change', function(d) {
					var select = d3.select(this);
					var option = select.node().options[select.node().selectedIndex];
					set_pool(option.value);
				});
				set_pool(null);
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
