define ['jquery'], ($) ->

  class BigPlayQueue
    constructor: (container) ->
      @__queue = []
      @__queued = {}
      @__container = container
      @__started = false

    start: ->
      return if @__started
      @__started = true
      @__showNext()

    stop: ->
      return unless @__started
      @__started = false
      @__hideCurrent()
      @__queue = []

    started: ->
      return @__started

    push: (popup) ->
      return unless popup
      id = popup.get_bigPlay().get_id()
      return if @__queued[id]
      @__queued[id] = true
      @__queue.push popup
      @__showNext() if @__started and not @__activeEntry
      return

    __showNext: ->
      return if @__activeEntry
      return if @__queue.length < 1

      @__activeEntry = @__queue[0]
      @__queue.shift()

      @__activeEntry.initialize()
      @__container.append @__activeEntry.get_element()

      @__activeEntry.show $.proxy @__afterShowNext, @
      return

    __afterShowNext: ->
      @__timer = window.setTimeout $.proxy(@__hideCurrent, @), 10000
      return

    __hideCurrent: ->
      return unless @__activeEntry
      window.clearTimeout @__timer
      @__timer = null
      @__activeEntry.hide $.proxy @__afterHideCurrent, @
      return

    __afterHideCurrent: ->
      @__activeEntry.get_element().remove()
      @__activeEntry = null
      @__showNext() if @__started and (@__queue.length > 0)
      return
