define ->
  
  graphContainer = null
  graphControlsContainer = null

  teamData = null
  relData = null

  chord = null
  color = null
  arc = null

  svg = null

  groupAngles = []

  fade = (opacity) ->
    return (g, i) ->
      svg.selectAll('g.chord path')
        .filter((d) ->
          return d.source.index isnt i and d.target.index isnt i
        )
        .transition()
        .style('opacity', opacity)
      return

  merge_seasons = ->
    data = []
    for own season, seasonData of relData
      for teamrel, i in seasondata
        data[i] = data[i] ? []
        for pairrel, j in teamrel
          data[i][j] = if data[i][j] then data[i][j] + pairrel else pairrel
    return data

  arcTween = (d, i) ->
    start = groupAngles[i] ? startAngle: 0, endAngle: 0
    inter = d3.interpolate start, d
    return (t) ->
      return arc inter t

  drawtext = (update) ->
    divisions = {}

    update.append('text')
      .each((d, i) ->
        d.angle = (d.startAngle + d.endAngle) / 2
        team = teamData[i]
        div = team.conference + ' ' + team.division
        if divisions[div]
          if d.angle < divisions[div].lowAngle
            divisions[div].lowAngle = d.angle
          else if d.angle > divisions[div].highAngle
            divisions[div].highAngle = d.angle
        else
          divisions[div] = lowAngle: d.angle, highAngle: d.angle
        return
      )
      .attr('dy', '.35em')
      .attr('text-anchor', (d) ->
        return if d.angle > Math.PI then 'end' else null
      )
      .attr('transform', (d) ->
        transform ='rotate(' + (d.angle * 180 / Math.PI - 90) + ')' + 'translate(' + (600*.41+36) + ')'
        transform += 'rotate(180)' if d.angle > Math.PI
        return transform
      )
      .text((d) ->
        return teamData[d.index].abbreviation
      )
      .style('opacity', 0)
      .on('mouseover', fade .1)
      .on('mouseout', fade 1)
      .transition()
      .duration(500)
      .style('opacity', 1)

    divisionEntries = d3.entries divisions
    for entry in divisionEntries
      entry.value = (entry.value.lowAngle + entry.value.highAngle) / 2

    divisiondata = svg.append('g')
      .attr('class', 'divisions')
      .selectAll('text')
      .data(divisionEntries)

    divisiondata.enter().append('text')
      .attr('dy', '.35em')
      .attr('text-anchor', (d) ->
        return if d.value > Math.PI then 'end' else null
      )
      .attr('transform', (d) ->
        transform = 'rotate(' + (d.value * 180 / Math.PI - 90) + ')' + 'translate(' + (600*.41+86) + ')'
        transform += 'rotate(180)' if d.value > Math.PI
        return transform
      )
      .text((d) -> return d.key)
      .style('opacity', 0)
      .transition()
      .duration(500)
      .style('opacity', 1)

    return

  drawchords = ->
    svg.append('g')
      .attr('class', 'chord')
      .selectAll('path')
      .data(chord.chords)
      .enter().append('path')
      .style('stroke', (d) ->
        return d3.rgb(color d.source.index).darker()
      )
      .style('fill', (d) ->
        return color d.source.index
      )
      .attr('d', d3.svg.chord().radius(600*.41))
      .style('opacity', 0)
      .transition()
      .duration(500)
      .style('opacity', 1)
    return

  draw = ->
    groupdata = svg.selectAll('g.arc')
      .data(chord.groups)

    groupdata.enter().append('g')
      .attr('class', 'arc')
      .append('path')
      .style('fill', (d) ->
        return color d.index
      )
      .style('stroke', (d) ->
        return d3.rgb(color d.index).darker()
      )
      .on('mouseover', fade .1)
      .on('mouseout', fade 1)
      .transition()
      .duration(500)
      .attrTween('d', arcTween)
      .each('end', (d, i) ->
        groupAngles[i] = startAngle: d.startAngle, endAngle: d.endAngle
        return
      )

    groupdata.select('path')
      .transition()
      .duration(500)
      .attrTween('d', arcTween)
      .each('end', (d, i) ->
        groupAngles[i] = startAngle: d.startAngle, endAngle: d.endAngle
        if i is (groupdata[0].length - 1)
          drawtext groupdata
          drawchords()
        return
      )

    return

  set_season = (season) ->
    data = if season then relData[season] else merge_seasons()

    chord.matrix data

    labels = svg.selectAll 'g.arc text'
    lastlabel = labels[0].length-1

    svg.selectAll('g.chord')
      .transition()
      .duration(500)
      .style('opacity', 0)
      .remove()

    if lastlabel < 0
      draw()
      return

    svg.selectAll('g.divisions')
      .transition()
      .duration(500)
      .style('opacity', 0)
      .remove()

    labels.transition()
      .duration(500)
      .style('opacity', 0)
      .remove()
      .each('end', (d, i) ->
        if i is lastlabel
          draw()
        return
      )

    return

  initialize = ->
    chord = d3.layout.chord()
      .padding .05

    color = d3.scale.category20b()

    svg = d3.select(graphContainer)
      .append('svg')
      .attr('width', 900)
      .attr('height', 900)
      .append('g')
      .attr('transform', 'translate(450,450)')

    loading = svg.append('text')
      .attr('transform', 'translate(-40,0)')
      .attr('font-size', '20')
      .style('opacity', 1)
      .text('Loading...')

    arc = d3.svg.arc().innerRadius(600*.41).outerRadius(600*.41*1.1)

    d3.json 'index.php?a=graphdata&g=teamrel', (data) ->
      teamData = data.teams
      relData = data.games

      loading.transition()
        .duration(500)
        .style('opacity', 0)
        .remove()

      seasonselect = d3.select(graphControlsContainer)
        .append('select')
        .style('font-size', 'larger')

      seasonarray = []
      for own season of relData
        seasonarray.push season
      seasonarray.sort()
      seasonarray.reverse()

      firstseason = null
      for seasonyear in seasonarray
        option = seasonselect.append('option')
        option.attr('value', seasonyear)
          .text(seasonyear + '-' + (parseInt(seasonyear)+1))
          if not firstseason
            option.attr 'selected', 'selected'
            firstseason = seasonyear

      seasonselect.on 'change', (d) ->
        select = d3.select this
        option = select.node().options[select.node().selectedIndex]
        set_season(option.value)
        return

      set_season firstseason

      return

    return

  return (container, controlscontainer) ->
    graphContainer = container
    graphControlsContainer = controlscontainer

    require ['d3'], ->
      initialize()
      return

    return

