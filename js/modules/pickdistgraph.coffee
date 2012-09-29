define ->

  width = 500
  height = 500

  innerRadius = 100
  outerRadius = 200
  growthRadius = 20

  pieDuration = 750
  growDuration = 250

  graphContainer = null
  graphControlsContainer = null

  arc = null
  grownArc = null
  pie = null
  color = null

  svg = null

  data = null

  pieTween = (d) ->
    start =
      startAngle: if this._current then this._current.startAngle else 0
      endAngle: if this._current then this._current.endAngle else 0
    end =
      startAngle: d.startAngle
      endAngle: d.endAngle
    i = d3.interpolate start, end
    this._current = i 0
    return (t) ->
      return arc i t

  removePieTween = (d) ->
    start =
      startAngle: d.startAngle
      endAngle: d.endAngle
    end =
      startAngle: 0
      endAngle: 0
    i = d3.interpolate start, end
    this._current = i(0)
    return (t) ->
      return arc i t

  combinedData = ->
    cdata = {}

    for own pool, pooldata of data
      continue unless pooldata.picks

      for own team, pickcount of pooldata.picks
        cdata[team] = if cdata[team] then cdata[team] + pickcount else pickcount

    return cdata

  set_pool = (pool) ->
    pooldata = if pool then data[pool].picks else combinedData()

    dataEntries = d3.entries pooldata
    count = 0
    dataEntries.forEach (d) ->
      count += d.value
      return

    svg.select('text.count')
      .text count

    svg.select('text.picks')
      .text if count is 1 then 'pick' else 'picks'

    arcGroup = svg.select('g.arcs')
    paths = arcGroup.selectAll('path')
      .data pie(dataEntries), (d) ->
        d.key

    paths.enter().append('path')
      .attr('fill', (d, i) -> color i)
      .transition()
      .duration(pieDuration)
      .attrTween('d', pieTween)

    paths
      .transition()
      .duration(pieDuration)
      .attrTween('d', pieTween)

    paths.exit()
      .transition()
      .duration(pieDuration)
      .attrTween('d', removePieTween)
      .remove()

    arcGroup.selectAll('path')
      .on('mouseover', (d) ->
        d3.select(this)
          .transition()
          .duration(growDuration)
          .attr('d', grownArc)
        svg.select('text.team')
          .transition()
          .style('opacity', 1)
          .text d.data.key
        svg.select('text.count')
          .text d.data.value
        svg.select('text.picks')
          .text if d.data.value is 1 then 'pick' else 'picks'
        return
      )
      .on('mouseout', (d) ->
        d3.select(this)
          .transition()
          .duration(growDuration)
          .attr('d', arc)
        svg.select('text.team')
          .transition()
          .style('opacity', 0)
        svg.select('text.count')
          .text count
        svg.select('text.picks')
          .text if count is 1 then 'pick' else 'picks'
        return
      )
    return

  initialize = ->
    pie = d3.layout.pie().value (d) ->
      return d.value
    color = d3.scale.category20()

    svg = d3.select(graphContainer).append('svg')
      .attr('width', width)
      .attr('height', height)

    placeholderGroup = svg.append('g')
      .attr('class', 'placeholder')
      .attr('transform', 'translate(' + (width/2) + ',' + (height/2) + ')')

    placeholderGroup.append('path')
      .attr('fill', '#EFEFEF')
      .attr('d', d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius).startAngle(0).endAngle(6.28318531)())

    arcGroup = svg.append('g')
      .attr('class', 'arcs')
      .attr('transform', 'translate(' + (width/2) + ',' + (height/2) + ')')

    centerGroup = svg.append('g')
      .attr('transform', 'translate(' + (width/2) + ',' + (height/2) + ')')

    teamLabel = centerGroup.append('text')
      .attr('class', 'team')
      .attr('dy', -25)
      .attr('font-size', '20')
      .attr('text-anchor', 'middle')
      .style('opacity', 0)

    countLabel = centerGroup.append('text')
      .attr('class', 'count')
      .attr('dy', 0)
      .attr('text-anchor', 'middle')
      .attr('font-size', '20')
      .text('Loading')

    picksLabel = centerGroup.append('text')
      .attr('class', 'picks')
      .attr('dy', 20)
      .attr('text-anchor', 'middle')
      .attr('fill', 'gray')
      .attr('font-size', '16')
      .text('picks')

    arc = d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius)
    grownArc = d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius+growthRadius)
    
    d3.json 'index.php?a=graphdata&g=pickdist', (graphdata) ->
      data = graphdata

      controlsContainer = d3.select graphControlsContainer

      poolselect = controlsContainer.append('select')
        .style('font-size', 'larger')
      poolselect.append('option')
        .attr('value', '')
        .text('All pools')

      for own pool, pooldata of data
        poolselect.append('option')
          .attr('value', pool)
          .text(pooldata.name + ' [' + pooldata.season + '-' + (pooldata.season + 1) + ']')
      poolselect.on 'change', (d) ->
        select = d3.select this
        option = select.node().options[select.node().selectedIndex]
        set_pool option.value

      set_pool null
      return

  return (graphcontainer, graphcontrolscontainer) ->
    graphContainer = graphcontainer
    graphControlsContainer = graphcontrolscontainer

    require ['d3'], ->
      initialize()
      return
    return
