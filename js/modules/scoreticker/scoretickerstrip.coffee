define ['jquery', 'cs!./gametile', 'cs!./bigplaypopup', 'cs!./bigplayqueue'], ($, GameTile, BigPlayPopup, BigPlayQueue) ->
  
  class ScoreTickerStrip
    constructor: (engine) ->
      if engine
        @__engine = engine
        engine.addObserver this
      @__gameTiles = {}
      @__observers = []

    initialize: ->
      @__initElements()
      @__bigPlayQueue = new BigPlayQueue @__container
      @__bigPlayQueue.start() if @__engine.started()
      return

    __initElements: ->
      @__container = $ document.createElement 'div'
      @__container.addClass 'tickerContainerDiv'

      @__gameTable = $ document.createElement 'table'
      @__gameTable.addClass 'tickerGameTable'

      @__gameRow = $ document.createElement 'tr'

      @__gameTable.append @__gameRow
      @__container.append @__gameTable

    __addGameTile: (game) ->
      return unless game
      gsis = game.get_gsis()
      td = $ document.createElement 'td'
      td.addClass 'tickerGameCell'
      gameTile = new GameTile game
      gameTile.initialize()
      @__gameTiles[gsis] = gameTile
      td.append gameTile.get_element()
      @__gameRow.append td
      return

    __removeGameTile: (game) ->
      return unless game
      gsis = game.get_gsis()
      if @__gameTiles[gsis]
        @__gameTiles[gsis].get_element().parent().remove()
        delete @__gameTiles[gsis]
      return

    __addBigPlayPopup: (bigPlay) ->
      return unless bigPlay
      count = 0
      for own gsis of @__gameTiles
        count += 1
      half = Math.ceil count/2
      idx = 0
      for own gsis, gameTile of @__gameTiles
        continue unless gameTile
        idx += 1
        if gsis is bigPlay.get_gsis()
          popup = new BigPlayPopup bigPlay, gameTile, (idx>=half)
          @__bigPlayQueue.push popup
          return

    get_element: ->
      return @__container

    get_width: ->
      return @__gameTable.width()

    observeChange: (object, changeType, changeData) ->
      return unless object is @__engine
      return unless changeType is 'propertychanged'
      for own key, value of changeData
        switch key
          when 'addedGames'
            for gameTile in value
              @__addGameTile gameTile
            @__notify 'widthchanged'
          when 'removedGames'
            for gameTile in value
              @__removeGameTile gameTile
            @__notify 'widthchanged'
          when 'addedBigPlays'
            for bigPlay in value
              @__addBigPlayPopup bigPlay
          when 'started'
            if value then @__bigPlayQueue.start()
            else @__bigPlayQueue.stop()
      return

    addObserver: (observer) ->
      return unless observer
      return if observer in @__observers
      @__observers.push observer
      return

    removeObserver: (observer) ->
      return unless observer
      for obs, i in @__observers
        if obs is observer
          @__observers.splice i, 1
          return

    __notify: (changeType) ->
      return unless @__observers.length > 0
      for observer in @__observers
        observer.observeChange this, changeType if typeof observer.observeChange is 'function'
      return
