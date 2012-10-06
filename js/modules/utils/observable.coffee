define
  addObserver: (observer) ->
    return unless observer
    return unless typeof observer.observeChange is 'function'
    if @__observers
      return if observer in @__observers
    else
      @__observers = []
    @__observers.push observer
    return

  removeObserver: (observer) ->
    return unless observer
    return unless @__observers
    for obs, i in @__observers
      if obs is observer
        @__observers.splice i, 1
        return
    return

  __hasObservers: ->
    return @__observers and (@__observers.length > 0)

  __notifyObservers: (changeType, changeData) ->
    return unless @__hasObservers() and changeType
    for observer in @__observers
      observer.observeChange @, changeType, changeData
    return
