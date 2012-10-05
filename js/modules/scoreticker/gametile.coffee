define ['jquery'], ($, Game) ->

  class GameTile
    constructor: (game) ->
      if game
        @__game = game
        game.addObserver this
      @__highlight = false

    get_game: ->
      return @__game

    get_element: ->
      return @__base

    get_position: ->
      return @__table.position()

    get_width: ->
      return @__table.width()

    get_height: ->
      return @__table.height()

    initialize: ->
      @__initElements()
      @__initState()
      return

    __initElements: ->
      @__base = $ document.createElement 'a'

      @__table = $ document.createElement 'table'
      @__table.addClass 'tickerGameTile'

      visitorRow = $ document.createElement 'tr'
      @__visitorCell = $ document.createElement 'td'
      @__visitorCell.addClass 'tickerGameTeam'
      visitorRow.append @__visitorCell

      @__visitorPossessionCell = $ document.createElement 'td'
      @__visitorPossessionCell.addClass 'tickerPossession'
      visitorRow.append @__visitorPossessionCell

      @__visitorScoreCell = $ document.createElement 'td'
      @__visitorScoreCell.addClass 'tickerGameScore'
      visitorRow.append @__visitorScoreCell

      @__table.append visitorRow

      homeRow = $ document.createElement 'tr'
      @__homeCell = $ document.createElement 'td'
      @__homeCell.addClass 'tickerGameTeam'
      homeRow.append @__homeCell

      @__homePossessionCell = $ document.createElement 'td'
      @__homePossessionCell.addClass 'tickerPossession'
      homeRow.append @__homePossessionCell

      @__homeScoreCell = $ document.createElement 'td'
      @__homeScoreCell.addClass 'tickerGameScore'
      homeRow.append @__homeScoreCell

      @__table.append homeRow

      statusRow = $ document.createElement 'tr'
      @__statusCell = $ document.createElement 'td'
      @__statusCell.addClass 'tickerGameStatus'
      @__statusCell.attr 'colspan', '3'
      statusRow.append @__statusCell

      @__table.append statusRow

      @__base.attr 'target', '_blank'
      @__base.append @__table
      return

    __initState: ->
      return unless @__game

      game = @__game

      @__table.addClass 'tickerGameRedZone' if game.get_redZone()

      @__visitorCell.text game.get_visitor()
      @__homeCell.text game.get_home()

      if game.playing()
        @__visitorPossessionCell.text '<' if game.get_possession() is game.get_visitor()
        @__homePossessionCell.text '<' if game.get_possession() is game.get_home()

      quarter = game.get_quarter()
      if quarter isnt 'P'
        @__visitorScoreCell.text game.get_visitorScore()
        @__homeScoreCell.text game.get_homeScore()

      @__statusCell.text game.get_status()

      @__base.attr 'href', game.get_url()
      @__base.attr 'id', game.get_gsis()

      @__updateQuarter quarter
      return

    __updateQuarter: (quarter) ->
      return unless @__game

      switch quarter
        when 'P'
          @__table.removeClass 'tickerPlaying'
          @__table.removeClass 'tickerGameFinished'
          @__table.addClass 'tickerGamePending'
        when 'F', 'FO'
          @__table.removeClass 'tickerPlaying'
          @__table.removeClass 'tickerGamePending'
          @__table.addClass 'tickerGameFinished'
        else
          @__table.removeClass 'tickerGameFinished'
          @__table.removeClass 'tickerGamePending'
          @__table.addClass 'tickerPlaying'

      if quarter isnt 'P'
        @__homeScoreCell.text @__game.get_homeScore()
        @__visitorScoreCell.text @__game.get_visitorScore()

      visitorwin = false
      homewin = false
      if (quarter is 'F') or (quarter is 'FO')
        vs = +@__game.get_visitorScore()
        hs = +@__game.get_homeScore()
        homewin = true if hs > vs
        visitorwin = true if vs > hs

      if visitorwin
        @__visitorCell.addClass 'tickerTeamWinner'
        @__visitorScoreCell.addClass 'tickerTeamWinner'
      else
        @__visitorCell.removeClass 'tickerTeamWinner'
        @__visitorScoreCell.removeClass 'tickerTeamWinner'

      if homewin
        @__homeCell.addClass 'tickerTeamWinner'
        @__homeScoreCell.addClass 'tickerTeamWinner'
      else
        @__homeCell.removeClass 'tickerTeamWinner'
        @__homeScoreCell.removeClass 'tickerTeamWinner'

      return

    get_highlight: ->
      return @__highlight

    set_highlight: (highlight) ->
      return if highlight is @__highlight
      @__highlight = highlight
      if highlight
        @__table.addClass 'tickerGameTileHighlighted'
      else
        @__table.removeClass 'tickerGameTileHighlighted'
      return

    observeChange: (object, changeType, changeData) ->
      return unless object is @__game
      return unless changeType is 'propertychanged'
      return unless changeData
      for own key, value of changeData
        switch key
          when 'status' then @__statusCell.text value
          when 'url' then @__base.attr 'href', value
          when 'visitor' then @__visitorCell.text value
          when 'home' then @__homeCell.text value
          when 'homeScore' then @__homeScoreCell.text value if @__game.get_quarter() isnt 'P'
          when 'visitorScore' then @__visitorScoreCell.text value if @__game.get_quarter() isnt 'P'
          when 'redZone'
            if value
              @__table.addClass 'tickerGameRedZone'
            else
              @__table.removeClass 'tickerGameRedZone'
          when 'quarter' then @__updateQuarter value
          when 'possession'
            if @__game.playing() and (value is @__game.get_visitor())
              @__visitorPossessionCell.text '<'
            else
              @__visitorPossessionCell.text ''
            if @__game.playing() and (value is @__game.get_home())
              @__homePossessionCell.text '<'
            else
              @__homePossessionCell.text ''
      return
