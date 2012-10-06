define ['jquery', 'cs!./scoretickerengine', 'cs!./scoretickerstrip', 'cookies'], ($, ScoreTickerEngine, ScoreTickerStrip) ->
  
  class ScoreTicker
    constructor: (element) ->
      @__boundElement = $ element
      @__hidden = false

    initialize: ->
      if $.cookies.test()
        ck = $.cookies.get('ToteScoretickerHidden')
        @__hidden = ck if ck?
      
      @__initElements()

      @__engine = new ScoreTickerEngine()
      @__engine.addObserver this

      @__strip = new ScoreTickerStrip @__engine
      @__strip.addObserver this
      @__strip.initialize()

      stripelement = @__strip.element()
      stripelement.hide()
      @__contentDiv.append stripelement

      @__engine.start() unless @__hidden
      return

    __initElements: ->
      @__toggleDiv = $ document.createElement 'div'
      @__toggleDiv.addClass 'tickerToggleDiv'
      @__toggleDiv.addClass 'subSection'

      @__toggleLink = $ document.createElement 'a'
      @__toggleLink.addClass 'tickerToggleLink'

      if @__hidden
        @__toggleLink.text 'Score ticker...'
        @__toggleLink.addClass 'tickerClosed'
        @__toggleLink.toggle $.proxy(@__showClick, @), $.proxy(@__hideClick, @)
      else
        @__toggleLink.text 'Score ticker'
        @__toggleLink.addClass 'tickerOpen'
        @__toggleLink.toggle $.proxy(@__hideClick, @), $.proxy(@__showClick, @)
      
      @__toggleLink.attr 'href', '#'

      @__toggleDiv.append @__toggleLink

      @__contentDiv = $ document.createElement 'div'
      @__contentDiv.hide() if @__hidden
      @__toggleDiv.append @__contentDiv

      @__titleDiv = $ document.createElement 'div'
      @__titleDiv.addClass 'tickerTitle'
      
      @__titleSpan = $ document.createElement 'span'
      @__titleSpan.text 'Loading...'
      
      @__titleDiv.append @__titleSpan

      @__loaderImage = $ document.createElement 'img'
      @__loaderImage.attr 'src', 'images/scoreticker-loader.gif'
      @__loaderImage.css 'margin-left', '10px'
      @__loaderImage.css 'display', 'inline-block'

      @__titleDiv.append @__loaderImage

      @__contentDiv.append @__titleDiv

      @__boundElement.width 650
      @__boundElement.addClass 'rounded-bottom' if @__hidden
      @__boundElement.append @__toggleDiv
      return

    __showClick: (event) ->
      @show()
      return false

    __hideClick: (event) ->
      @hide()
      return false

    show: ->
      return unless @__hidden
      @__toggleLink.text 'Score ticker...'
      @__toggleLink.removeClass 'tickerClosed'
      @__toggleLink.addClass 'tickerOpen'
      @__boundElement.removeClass 'rounded-bottom'
      @__contentDiv.show 'fast', $.proxy(@__startEngine, @)
      if $.cookies.test()
        exp = new Date()
        exp.setDate exp.getDate()+365
        $.cookies.set 'ToteScoretickerHidden', false, expiresAt: exp
      @__hidden = false
      return

    __startEngine: ->
      @__engine.start()
      return

    hide: ->
      return if @__hidden
      @__engine.stop()
      @__contentDiv.hide 'fast'
      @__boundElement.addClass 'rounded-bottom'
      @__toggleLink.text 'Score ticker...'
      @__toggleLink.removeClass 'tickerOpen'
      @__toggleLink.addClass 'tickerClosed'
      if $.cookies.test()
        exp = new Date()
        exp.setDate exp.getDate()+365
        $.cookies.set 'ToteScoretickerHidden', true, expiresAt: exp
      @__hidden = true
      return

    observeChange: (object, changeType, changeData) ->
      if object is @__engine
        switch changeType
          when 'propertychanged'
            for own key, value of changeData
              switch key
                when 'weekString' then @__titleSpan.text value
          when 'datarequested' then @__loaderImage.fadeTo 'slow', 1
          when 'datareceived' then @__loaderImage.fadeTo 'slow', 0
      else if object is @__strip
        switch changeType
          when 'widthchanged'
            if not @__hidden
              @__strip.element().slideDown 'fast'
              @__boundElement.animate width: (@__strip.width()+4)+'px', 'fast'
      return
