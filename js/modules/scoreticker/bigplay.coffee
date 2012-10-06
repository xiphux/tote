define ['cs!modules/utils/mixin', 'cs!modules/utils/observable'], (mixin, observable) ->
  
  class BigPlay
    constructor: ->
      mixin this, observable
      @__updates = {}

    eid: (eid) ->
      return @__eid if typeof eid is 'undefined'

      return @ if eid is @__eid
      @__eid = eid
      @__queueUpdates eid: eid
      return @

    gsis: (gsis) ->
      return @__gsis if typeof gsis is 'undefined'

      return @ if gsis is @__gsis
      @__gsis = gsis
      @__queueUpdates gsis: gsis
      return @

    id: (id) ->
      return @__id if typeof id is 'undefined'

      return @ if id is @__id
      @__id = id
      @__queueUpdates id: id
      return @

    team: (team) ->
      return @__team if typeof team is 'undefined'

      return @ if team is @__team
      @__team = team
      @__queueUpdates team: team
      return @

    message: (message) ->
      return @__message if typeof message is 'undefined'

      return @ if message is @__message
      @__message = message
      @__queueUpdates message: message
      return @

    data: (data) ->
      return unless data

      @__delayNotify = true

      for own key, value of data
        switch key
          when 'eid' then @eid value
          when 'gsis' then @gsis value
          when 'id' then @id value
          when 'team' then @team value
          when 'message' then @message value

      @__delayNotify = false
      @__notify()
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
      return unless @__hasObservers()

      modified = false
      for own key of @__updates
        modified = true
        break
      return unless modified

      @__notifyObservers 'propertychanged', @__updates
      
      @__updates = {}
      return
