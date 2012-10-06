define ['jquery', 'cs!./game', 'cs!./bigplay', 'cs!modules/utils/mixin', 'cs!modules/utils/observable'], ($, Game, BigPlay, mixin, observable) ->

  class ScoreTickerEngine
    constructor: ->
      mixin this, observable
      @__games = {}
      @__bigPlays = {}

      @__updates = {}
      @__addedGames = []
      @__removedGames = []
      @__addedBigPlays = []
      @__removedBigPlays = []

      @__refreshInterval = 15
      @__started = false

    start: ->
      return if @__started
      @__started = true
      @update()
      @__updates.started = true
      @__notify 'propertychanged'
      return

    stop: ->
      return unless @__started
      @__started = false
      if @__timer
        window.clearTimeout @__timer
        @__timer = null
      @__updates.started = false
      @__notify 'propertychanged'

    started: ->
      return @__started

    update: ->
      if @__timer
        window.clearTimeout @__timer
        @__timer = null
      @__notify 'datarequested'

      $.get 'scoreticker.php', {}, $.proxy(@__updateSuccess, @), 'xml'
      return

    __updateSuccess: (xml) ->
      xmlData = $ xml
      gms = xmlData.find 'gms'
      @__updateInfo gms.attr('y'), gms.attr('w'), gms.attr('t')
      @__updateGames gms
      @__updateBigPlays xmlData.find('bps')
      if (gms.attr('gd') is '1') and @hasActiveGames()
        @refreshInterval 15
      else
        @refreshInterval 300
      @__notify('propertychanged')
      @__notify('datareceived')
      @__timer = window.setTimeout $.proxy(@update, @), @__refreshInterval*1000 if @__started
      return

    __updateInfo: (year, week, type) ->
      if year isnt @__year
        @__year = year
        @__updates.year = year
      if week isnt @__week
        @__week = week
        @__updates.week = week
      if type isnt @__type
        @__type = type
        @__updates.type = type
      return

    __updateGames: (gms) ->
      return unless gms
      updated = []
      games = @__games
      addedGames = @__addedGames
      gms.find('g').each ->
        g = $ this
        gsis = g.attr 'gsis'
        return unless gsis

        gameData =
          year: gms.attr 'y'
          seasonType: gms.attr 't'
          week: gms.attr 'w'
          eid: g.attr 'eid'
          start:
            day: g.attr 'd'
            time: g.attr 't'
          visitor: g.attr 'v'
          visitorNickname: g.attr 'vnn'
          home: g.attr 'h'
          homeNickname: g.attr 'hnn'
          quarter: g.attr 'q'
          clock: g.attr 'k'
          redZone: if g.attr('rz') is '1' then true else false
          possession: g.attr 'p'
          homeScore: g.attr 'hs'
          visitorScore: g.attr 'vs'

        if games[gsis]
          game = games[gsis]
        else
          game = new Game()
          games[gsis] = game
          addedGames.push game
          gameData.gsis = gsis

        game.data gameData

        updated[gsis] = true

        game = null
        g = null
        return

      for own gsis, gameobj of games
        continue if updated[gsis]
        @__removedGames.push gameobj
        delete games[gsis]
      return

    __updateBigPlays: (bps) ->
      return unless bps
      updated = []
      bigPlays = @__bigPlays
      addedBigPlays = @__addedBigPlays
      bps.find('b').each ->
        b = $ this
        id = b.attr 'id'
        return unless id

        bpData =
          gsis: b.attr 'gsis'
          eid: b.attr 'eid'
          team: b.attr 'abbr'
          message: b.attr 'x'
          id: id

        if bigPlays[id]
          bp = bigPlays[id]
        else
          bp = new BigPlay()
          bigPlays[id] = bp
          addedBigPlays.push bp

        bp.data bpData

        updated[id] = true

        bp = null
        b = null
        return

      for id, bigPlay of bigPlays
        continue if updated[id]
        @__removedBigPlays.push bigPlay
        delete bigPlays[id]
      return

    refreshInterval: (refreshInterval) ->
      return @__refreshInterval if typeof refreshInterval is 'undefined'

      return @ if refreshInterval is @__refreshInterval
      @__refreshInterval = refreshInterval
      return @

    weekString: ->
      return @__year + '-' + (@__year*1+1) + ' ' + (if @__type is 'P' then 'preseason' else '') + 'week ' + @__week

    hasActiveGames: ->
      for own gsis, game of @__games
        return true if game and game.active()
      return false

    __notify: (changeType) ->
      return unless @__hasObservers()
      changeData = null
      if changeType is 'propertychanged'
        modified = false
        changeData = @__updates
        if @__addedBigPlays.length > 0
          modified = true
          changeData.addedBigPlays = @__addedBigPlays
        if @__removedBigPlays.length > 0
          modified = true
          changeData.removedBigPlays = @__removedBigPlays
        if @__addedGames.length > 0
          modified = true
          changeData.addedGames = @__addedGames
        if @__removedGames.length > 0
          modified = true
          changeData.removedGames = @__removedGames
        if not modified
          for own key, value of changeData
            modified = true
            break
        return unless modified
        changeData.weekString = @weekString() if changeData.year or changeData.week or changeData.type

        @__updates = {}
        @__addedGames = []
        @__removedGames = []
        @__addedBigPlays = []
        @__removedBigPlays = []

      @__notifyObservers changeType, changeData
      return
