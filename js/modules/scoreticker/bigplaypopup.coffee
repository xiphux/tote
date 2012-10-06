define ['jquery'], ($) ->

  class BigPlayPopup
    constructor: (bigPlay, gameTile, reverse) ->
      @__bigPlay = bigPlay
      @__gameTile = gameTile
      @__reverse = reverse

    initialize: ->
      @__initElement()
      @__initState()

    __initElement: ->
      @__popup = $ document.createElement 'div'
      @__popup.addClass 'tickerBigPlay'
      @__popup.width 0

      @__content = $ document.createElement 'div'
      @__content.addClass 'tickerBigPlayContent'
      if @__reverse
        @__content.addClass 'tickerBigPlayLeft'
      else
        @__content.addClass 'tickerBigPlayRight'

      @__team = $ document.createElement 'div'
      @__team.addClass 'tickerBigPlayTeam'
      @__content.append @__team

      @__message = $ document.createElement 'div'
      @__message.addClass 'tickerBigPlayMessage'
      @__content.append @__message

      @__popup.append @__content
      return

    __initState: ->
      @__team.text @__bigPlay.team()
      message = @__bigPlay.message()
      message = message.substr 0, 52 + '...' if message.length > 52
      @__message.text message
      return

    show: (callback) ->
      return if @__visible

      pos = @__gameTile.position()
      @__popup.css 'top', pos.top+'px'

      left = pos.left+1
      left += @__gameTile.width()+1  if not @__reverse
      @__popup.css 'left', left+'px'

      @__popup.height @__gameTile.height()

      anim = width: '150px'
      anim.left = (left - 150) + 'px' if @__reverse

      @__gameTile.highlight true

      @__popup.animate anim, 400, 'swing', callback ? null

      @__visible = true
      return

    hide: (callback) ->
      return unless @__visible
      anim = width: '0px'
      anim.left = @__gameTile.position().left + 'px' if @__reverse
      @__popup.animate anim, 400, 'swing', =>
        @__gameTile.highlight false
        callback() if callback
        return
      @__visible = false
      return

    bigPlay: ->
      return @__bigPlay

    gameTile: ->
      return @__gameTile

    visible: ->
      return @__visible

    element: ->
      return @__popup

    observeChange: (object, changeType, changeData) ->
      return unless object is @__bigPlay
      return unless changeType is 'propertychanged'
      return unless changeData
      for own key, value of changeData
        switch key
          when 'team' then @__team.text value
          when 'message'
            value = value.substr 0, 52 + '...' if value.length > 52
            @__message.text value
      return
