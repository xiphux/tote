define ->
  
  class BigPlay
    constructor: ->
      @__observers = []
      @__updates = {}

    get_eid: ->
      return @__eid

    set_eid: (eid) ->
      return if eid is @__eid
      @__eid = eid
      @__queueUpdates eid: eid
      return

    get_gsis: ->
      return @__gsis

    set_gsis: (gsis) ->
      return if gsis is @__gsis
      @__gsis = gsis
      @__queueUpdates gsis: gsis
      return

    get_id: ->
      return @__id

    set_id: (id) ->
      return if id is @__id
      @__id = id
      @__queueUpdates id: id
      return

    get_team: ->
      return @__team

    set_team: (team) ->
      return if team is @__team
      @__team = team
      @__queueUpdates team: team
      return

    get_message: ->
      return @__message

    set_message: (message) ->
      return if message is @__message
      @__message = message
      @__queueUpdates message: message
      return

    set_data: (data) ->
      return unless data

      @__delayNotify = true

      for own key, value of data
        switch key
          when 'eid' then @set_eid value
          when 'gsis' then @set_gsis value
          when 'id' then @set_id value
          when 'team' then @set_team value
          when 'message' then @set_message value

      @__delayNotify = false
      @__notify()
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

    __queueUpdates: (updates) ->
      return unless updates
      modified = false
      for own key, value of updates
        @__updates[key] = value
        modified = true
      @__notify() if modified and not @__delayNotify
      return

    __notify: ->
      return unless @__observers.length > 0

      modified = false
      for own key of @__updates
        modified = true
        break
      return unless modified

      for observer in @__observers
        observer.observeChange this, 'propertychanged', @__updates if typeof observer.observeChange is 'function'
      @__updates = {}
      return
