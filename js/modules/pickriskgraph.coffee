define ->

  width = 900
  height = 950
  headerheight = 50

  graphContainer = null
  graphControlsContainer = null

  svg = null

  yscale = null
  xscale = null
  color = null
  yaxis = null
  line = null

  pooldata = null
  activedata = null

  readable_name = (user) ->
    return user.username if not user.first_name
    return if user.last_name then user.first_name + ' ' + user.last_name else user.first_name

  highlight_all_users = ->
    svg.select('text.header1')
      .transition()
      .duration(500)
      .style('opacity', 0)

    svg.select('text.header2')
      .transition()
      .duration(500)
      .style('opacity', 0)

    svg.selectAll('g.user path')
      .transition()
      .duration(500)
      .style('opacity', 1)

    svg.select('g.games').selectAll('circle')
      .transition()
      .duration(500)
      .style('opacity', 0)
      .attr('r', 0)
      .attr('fill', 'black')
    return

  highlight_user = (username) ->

    if not username
      highlight_all_users()
      return

    userdata = null
    for udata in activedata
      if udata.user.username is username
        userdata = udata
        break

    if not userdata
      highlight_all_users()
      return

    svg.select('text.header1')
      .text(readable_name userdata.user)
      .transition()
      .duration(500)
      .style('opacity', 1)

    if userdata.averagespread?
      svg.select('text.header2')
        .text('Average pick spread: ' + userdata.averagespread)
        .transition()
        .duration(500)
        .style('opacity', 1)

    svg.select('g.users').selectAll('g.user path')
      .filter((d, i) -> return d.user.username != username)
      .transition()
      .duration(500)
      .style('opacity', .1)

    svg.select('g.games').selectAll('circle')
      .each((d, i) ->
        for pick in userdata.pickdata
          if pick.game is d.key
            d3.select(this)
              .attr('cy', yscale(pick.spread))
              .attr('fill', ->
                return 'blue' if pick.win > 0
                return 'red' if pick.win < 0
                return 'black'
              )
              .transition()
              .duration(500)
              .attr('r', 4)
              .style('opacity', 1)
            return
      )

    return

  spread_range = (entries, games) ->
    upperspread = 0
    lowerspread = 0
    for own user, userentry of entries
      for pick in userentry.picks
        game = games[pick.game]
        continue unless game.point_spread
        if game.point_spread > upperspread
          upperspread = game.point_spread
          lowerspread = game.point_spread * -1
    return [upperspread, lowerspread]

  calculate_entrant_data = (entry, games) ->
    dataset = []
    spreadsum = 0
    spreadcount = 0
    
    for pick in entry.picks
      game = games[pick.game]
      win = 0
      spread = 0

      if game.point_spread and game.favorite and pick.team
        if ((pick.team is game.home_team) and (game.home_score > game.away_score)) or
        ((pick.team is game.away_team) and (game.away_score > game.home_score))
          win = 1
        else if ((pick.team is game.home_team) and (game.home_score < game.away_score)) or
        ((pick.team is game.away_team) and (game.away_score < game.home_score))
          win = -1

        spread = game.point_spread
        spread *= -1 if pick.team isnt game.favorite

        spreadsum += spread
        spreadcount += 1

      dataset.push
        week: pick.week
        spread: spread
        win: win
        game: pick.game

    dataset.sort (a, b) ->
      return -1 if a.week < b.week
      return 1 if a.week > b.week
      return 0

    dataobj =
      user: entry.user
      pickdata: dataset
      averagespread: if spreadcount > 0 then Math.round((spreadsum/spreadcount)*10)/10 else 0

    return dataobj

  calculate_pool_data = (entries, games) ->
    entriesdata = []

    for entry in entries
      entriesdata.push calculate_entrant_data entry, games

    return entriesdata

  populate_userselect = (users) ->
    userselect = d3.select('select.users')
    userselect.selectAll('option').remove()
    userselect.append('option')
      .attr('value', '')
      .text('All users')
    users.sort (a, b) ->
      an = readable_name a
      bn = readable_name b
      an = an.toLowerCase()
      bn = bn.toLowerCase()
      return -1 if an < bn
      return 1 if an > bn
      return 0
    for user in users
      userselect.append('option')
        .attr('value', user.username)
        .text(readable_name user)
    return

  render_gamepoints = (games) ->
    gameentries = d3.entries games
    gamedata = svg.select('g.games')
      .selectAll('circle')
      .data gameentries, (d) -> return d.key

    gamedata.exit().remove()

    gamedata.attr 'cx', (d) -> return xscale d.value.week

    gamedata.enter().append('circle')
      .attr('cx', (d) -> return xscale d.value.week)
      .attr('r', 0)
      .attr('fill', 'black')
      .style('opacity', 0)
    return

  render_userpaths = (activedata) ->
    maindata = svg.select('g.users')
      .selectAll('g.user')
      .data activedata, (d) -> return d.user.username

    maindata.exit()
      .transition()
      .duration(500)
      .style('opacity', 0)
      .remove()

    maindata.select('path')
      .transition()
      .duration(500)
      .attr('d', (d) -> return line d.pickdata)
      .style('stroke', (d, i) -> return color i)

    newdata = maindata.enter().append('g')
      .attr('class', 'user')
      .style('opacity', 1)
    newdata.append('path')
      .attr('d', (d) -> return line d.pickdata)
      .style('fill', 'none')
      .style('stroke', (d, i) -> return color i)
      .style('stroke-width', 3)
      .style('opacity', 0)
      .on('mouseover', (d, i) ->
        userselect = d3.select('select.users')
        option = userselect.node().options[userselect.node().selectedIndex]
        if not option.value
          highlight_user d.user.username
        return
      )
      .on('mouseout', (d, i) ->
        userselect = d3.select('select.users')
        option = userselect.node().options[userselect.node().selectedIndex]
        if not option.value
          highlight_all_users()
        return
      )
      .transition()
      .duration(500)
      .style('opacity', 1)
    return

  render_axes = (weeks) ->
    yaxis.scale yscale
    svg.select('g.yaxis')
      .transition()
      .duration(500)
      .call(yaxis)

    xaxisdata = svg.select('g.xaxis')
      .selectAll('g')
      .data(xscale.ticks weeks)

    xaxisnewdata = xaxisdata.enter().append('g')

    xaxisnewdata.append('line')
      .attr('x1', xscale)
      .attr('x2', xscale)
      .attr('y1', 40+headerheight)
      .attr('y2', height-50)
      .style('stroke', '#ccc')
    xaxisnewdata.append('text')
      .attr('x', xscale)
      .attr('y', height-30)
      .attr('text-anchor', 'middle')
      .text((d) -> return d)

    midline = svg.select('line.midline')
    if midline.empty()
      midline = svg.append('line')
        .style('stroke', '#ccc')
    midline
      .attr('x1', 40)
      .attr('x2', width-20)
      .attr('y1', yscale 0)
      .attr('y2', yscale 0)
 
    return

  set_pool = (pool) ->
    return unless pool

    data = pooldata[pool]

    xscale.domain [1, data.weeks]

    yscale.domain spread_range data.entries, data.games

    activedata = calculate_pool_data data.entries, data.games

    users = []
    for entry in activedata
      users.push entry.user
    populate_userselect users

    render_gamepoints data.games
    render_userpaths activedata
    render_axes data.weeks

    return

  initialize = ->
    svg = d3.select(graphContainer)
      .append('svg')
      .attr('width', width)
      .attr('height', height)

    color = d3.scale.category20b()
    
    xscale = d3.scale.linear()
      .range([50, (width-20)])

    yscale = d3.scale.linear()
      .range([(40+headerheight), (height-30)])

    yaxis = d3.svg.axis()
      .orient('right')

    line = d3.svg.line()
      .x((d) -> return xscale d.week)
      .y((d) -> return yscale d.spread)
      .interpolate('cardinal')
      .tension(0.9)

    svg.append('g')
      .attr('class', 'xaxis')

    svg.append('g')
      .attr('class', 'yaxis')

    svg.append('g')
      .attr('class', 'users')

    svg.append('g')
      .attr('class', 'games')

    headergroup = svg.append('g')
      .attr('class', 'header')

    headergroup.append('text')
      .attr('class', 'header1')
      .attr('x', 0)
      .attr('y', 20)
      .attr('font-size', 24)
      .style('opacity', 0)

    headergroup.append('text')
      .attr('class', 'header2')
      .attr('x', 0)
      .attr('y', 40)
      .attr('font-size', 20)
      .style('opacity', 0)

    loadingtext = svg.append('text')
      .attr('x', width/2)
      .attr('y', height/2)
      .attr('font-size', 20)
      .attr('text-anchor', 'middle')
      .style('opacity', 1)
      .text('Loading...')

    d3.json 'index.php?a=graphdata&g=pickrisk', (data) ->
      pooldata = data

      loadingtext.transition()
        .duration(500)
        .style('opacity', 0)
        .remove()

      controls = d3.select(graphControlsContainer)
      poolselect = controls.append('select')
        .style('font-size', 'larger')
      
      firstpool = null
      for own pool, pdata of data
        poolselect.append('option')
          .attr('value', pool)
          .text(pdata.name + ' [' + pdata.season + '-' + (pdata.season+1) + ']')
        if (!firstpool)
          firstpool = pool

      poolselect.on 'change', (d) ->
        select = d3.select(this)
        option = select.node().options[select.node().selectedIndex]
        selectedpool = option.value

        userselect = d3.select('select.users')
        selecteduser = userselect.node().options[userselect.node().selectedIndex]
        if not selecteduser.value
          set_pool selectedpool
          return

        highlight_all_users()
        setTimeout(->
          set_pool selectedpool
          return
        , 500)
        return

      controls.append 'br'

      controls.append('select')
        .attr('class', 'users')
        .style('font-size', 'larger')
        .on('change', (d) ->
          select = d3.select(this)
          option = select.node().options[select.node().selectedIndex]
          highlight_all_users()
          highlight_user option.value if option.value
          return
        )

      svg.append('text')
        .attr('x', width/2)
        .attr('y', height-10)
        .attr('text-anchor', 'middle')
        .text('Week')

      svg.append('text')
        .attr('x', 0)
        .attr('y', 30+headerheight)
        .text('Favorite')

      svg.append('text')
        .attr('x', 0)
        .attr('y', height-10)
        .text('Underdog')

      set_pool firstpool

      return
    
    return

  return (container, controlscontainer) ->
    graphContainer = container
    graphControlsContainer = controlscontainer

    require ['d3'], ->
      initialize()
      return
    return
