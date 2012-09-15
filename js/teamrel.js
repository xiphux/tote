(function() {

	var graphContainer = null;
	var graphControlsContainer = null;

	var teamData = null;
	var relData = null;

	var chord = null;
	var color = null;

	var svg = null;

	function teamNameByIndex(index)
	{
		var team = teamData[index];
		return team.abbreviation;
	}

	function fade(opacity)
	{
		return function(g, i) {
			svg.selectAll('g.chord path')
				.filter(function(d) {
					return d.source.index != i && d.target.index != i;
				})
			.transition()
				.style('opacity', opacity);
		};
	}

	function merge_seasons()
	{
		var data = [];

		for (var season in relData) {
			if (!relData.hasOwnProperty(season))
				continue;

			for (var i = 0; i < relData[season].length; i++) {
				if (!data[i]) {
					data[i] = [];
				}
				for (var j = 0; j < relData[season][i].length; j++) {
					if (data[i][j])
						data[i][j] += relData[season][i][j];
					else
						data[i][j] = relData[season][i][j];
				}
			}
		}

		return data;
	}

	function draw()
	{
		var groupdata = svg.append("g")
			.selectAll("path")
			.data(chord.groups);

		var groups = groupdata.enter().append('g');

		groups.append('path')
			.attr('class', 'arc')
			.style("fill", function (d) { return color(d.index); })
			.style("stroke", function (d) { return d3.rgb(color(d.index)).darker(); })
			.attr("d", d3.svg.arc().innerRadius(600*.41).outerRadius(600*.41*1.1))
			.on('mouseover', fade(.1))
			.on('mouseout', fade(1));

		var divisions = {};

		groups.append('text')
			.each(function(d, i) { 
				d.angle = (d.startAngle + d.endAngle) / 2;
				var team = teamData[i];
				var division = team.conference + ' ' + team.division;
				if (divisions[division]) {
					divisions[division] = (divisions[division] + d.angle) / 2;
				} else {
					divisions[division] = d.angle;
				}
			})
			.attr('dy', '.35em')
			.attr('text-anchor', function(d) { return d.angle > Math.PI ? "end" : null; })
			.attr("transform", function(d) {
				return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
				+ "translate(" + (600*.41+36) + ")"
				+ (d.angle > Math.PI ? "rotate(180)" : "");
			})
			.text(function(d) { return teamNameByIndex(d.index); })
			.style('opacity', 0)
			.on('mouseover', fade(.1))
			.on('mouseout', fade(1))
			.transition()
			.duration(500)
			.style('opacity', 1);

		var divisionEntries = d3.entries(divisions);

		svg.append('g')
			.selectAll('path')
			.data(divisionEntries)
			.enter().append('text')
			.attr('dy', '.35em')
			.attr('text-anchor', function(d) { return d.value > Math.PI ? 'end' : null; })
			.attr('transform', function(d) {
				return 'rotate(' + (d.value * 180 / Math.PI - 90) + ')'
				+ 'translate(' + (600*.41+86) + ')'
				+ (d.value > Math.PI ? 'rotate(180)' : '');
			})
			.text(function(d) { return d.key; })
			.style('opacity', 0)
			.transition()
			.duration(500)
			.style('opacity', 1);

		svg.append("g")
			.attr('class', 'chord')
			.selectAll("path")
			.data(chord.chords)
			.enter().append("path")
			.style("stroke", function (d) { return d3.rgb(color(d.source.index)).darker(); })
			.style("fill", function(d) { return color(d.source.index); })
			.attr("d", d3.svg.chord().radius(600*.41))
			.style('opacity', 0)
			.transition()
			.duration(500)
			.style('opacity', 1);
	}

	function set_season(season)
	{
		var data = null;
		if (season)
			data = relData[season];
		else
			data = merge_seasons();

		chord.matrix(data);

		var labels = svg.selectAll('g text');
		var lastlabel = labels[0].length - 1;

		svg.selectAll('g path')
			.transition()
			.duration(500)
			.style('opacity', 0)
			.remove();

		if (lastlabel >= 0) {
			labels.transition()
				.duration(500)
				.style('opacity', 0)
				.remove()
				.each('end', function (d, i) {
					if (i == lastlabel)
						draw();
				});
		} else {
			draw();
		}

	}

	function initialize(container, controlscontainer)
	{
		graphContainer = container;
		graphControlsContainer = controlscontainer;

		chord = d3.layout.chord()
			.padding(.05);

		color = d3.scale.category20b();

		svg = d3.select(graphContainer)
			.append("svg")
			.attr('width', 900)
			.attr('height', 900)
			.append("g")
			.attr("transform", "translate(450,450)");

		d3.json('index.php?a=graphdata&g=teamrel', function(data) {
			teamData = data.teams;
			relData = data.games;

			var controlsContainer = d3.select(graphControlsContainer);
			var seasonselect = controlsContainer.append('select')
				.style('font-size', 'larger');
			seasonselect.append('option').attr('value', '').text('All seasons');
			var seasonarray = [];
			for (var season in relData) {
				if (relData.hasOwnProperty(season)) {
					seasonarray.push(season);
				}
			}
			seasonarray.sort();
			seasonarray.reverse();
			var firstseason = null;
			for (var i = 0; i < seasonarray.length; i++) {
				var seasonyear = seasonarray[i];
				var option = seasonselect.append('option');
				option.attr('value', seasonyear)
					.text(seasonyear + '-' + (parseInt(seasonyear)+1));
				if (!firstseason) {
					option.attr('selected', 'selected');
					firstseason = seasonyear;
				}
			}
			seasonselect.on('change', function(d) {
				var select = d3.select(this);
				var option = select.node().options[select.node().selectedIndex];
				set_season(option.value);
			});

			set_season(firstseason);
		});
	}

	$(document).ready(function() {
		initialize('#graph', '#graphControls');
	});

})()
