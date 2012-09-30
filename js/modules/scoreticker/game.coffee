define ['cs!./localstart'], (localStart) ->
  
  class Game
    constructor: ->
      @__observers = []
      @__updates = {}

    get_year: ->
      return @__year

    set_year: (year) ->
      return if year is @__year
      @__year = year
      @__queueUpdates year: year
      return

    get_seasonType: ->
      return @__seasonType

    set_seasonType: (seasonType) ->
      return if seasonType is @__seasonType
      @__seasonType = seasonType
      @__queueUpdates seasonType: seasonType
      return

    get_week: ->
      return @__week

    set_week: (week) ->
      return if week is @__week
      @__week = week
      @__queueUpdates week: week
      return

    get_gsis: ->
      return @__gsis

    set_gsis: (gsis) ->
      return if gsis is @__gsis
      @__gsis = gsis
      @__queueUpdates gsis: gsis
      return

    get_eid: ->
      return @__eid

    set_eid: (eid) ->
      return if eid is @__eid
      @__eid = eid
      @__queueUpdates eid: eid
      return

    get_start: ->
      return day: @__startDay, time: @__startTime

    set_start: (day, time) ->
      return if day is @__startDay and time is @__startTime
      @__startDay = day
      @__startTime = time

      shifted = localStart day, time
      @__localStartDay = shifted.day
      @__localStartTime = shifted.time

      @__queueUpdates
        startDay: day
        startTime: time
      return

    get_visitor: ->
      return @__visitor

    set_visitor: (visitor) ->
      return if visitor is @__visitor
      @__visitor = visitor
      @__queueUpdates visitor: visitor
      return

    get_visitorNickname: ->
      return @__visitorNickname

    set_visitorNickname: (visitorNickname) ->
      return if visitorNickname is @__visitorNickname
      @__visitorNickname = visitorNickname
      @__queueUpdates visitorNickname: visitorNickname
      return

    get_home: ->
      return @__home

    set_home: (home) ->
      return if home is @__home
      @__home = home
      @__queueUpdates home: home
      return

    get_homeNickname: ->
      return @__homeNickname

    set_homeNickname: (homeNickname) ->
      return if homeNickname is @__homeNickname
      @__homeNickname = homeNickname
      @__queueUpdates homeNickname: homeNickname
      return

    get_homeScore: ->
      return @__homeScore

    set_homeScore: (homeScore) ->
      return if homeScore is @__homeScore
      @__homeScore = homeScore
      @__queueUpdates homeScore: homeScore
      return

    get_visitorScore: ->
      return @__visitorScore

    set_visitorScore: (visitorScore) ->
      return if visitorScore is @__visitorScore
      @__visitorScore = visitorScore
      @__queueUpdates visitorScore: visitorScore
      return

    get_redZone: ->
      return @__redZone

    set_redZone: (redZone) ->
      return if redZone is @__redZone
      @__redZone = redZone
      @__queueUpdates redZone: redZone
      return

    get_quarter: ->
      return @__quarter

    set_quarter: (quarter) ->
      return if quarter is @__quarter
      @__quarter = quarter
      @__queueUpdates quarter: quarter
      return

    get_clock: ->
      return @__clock

    set_clock: (clock) ->
      return if clock is @__clock
      @__clock = clock
      @__queueUpdates clock: clock
      return

    get_possession: ->
      return @__possession

    set_possession: (possession) ->
      return if possession is @__possession
      @__possession = possession
      @__queueUpdates possession: possession
      return

    set_data: (data) ->
      return unless data

      @__delayNotify = true

      for own key, value of data
        switch key
          when 'year' then @set_year value
          when 'seasonType' then @set_seasonType value
          when 'week' then @set_week value
          when 'gsis' then @set_gsis value
          when 'eid' then @set_eid value
          when 'start' then @set_start value.day, value.time if value.day and value.time
          when 'visitor' then @set_visitor value
          when 'visitorNickname' then @set_visitorNickname value
          when 'home' then @set_home value
          when 'homeNickname' then @set_homeNickname value
          when 'homeScore' then @set_homeScore value
          when 'visitorScore' then @set_visitorScore value
          when 'redZone' then @set_redZone value
          when 'quarter' then @set_quarter value
          when 'clock' then @set_clock value
          when 'possession' then @set_possession value

      @__delayNotify = false
      @__notify()

    playing: ->
      return @__quarter? and
      (@__quarter isnt 'P') and
      (@__quarter isnt 'F') and
      (@__quarter isnt 'FO') and
      (@__quarter isnt 'H')

    active: ->
      return @playing() or @__quarter is 'H'

    get_url: ->
      typestr = ''
      switch @__seasonType
        when 'P' then typestr = 'PRE'
        when 'R' then typestr = 'REG'
        else return ''
      return 'http://www.nfl.com/gamecenter/' + @__eid + '/' + @__year + '/' + typestr + @__week + '/' + @__visitorNickname + '@' + @__homeNickname

    get_status: ->
      switch @__quarter
        when 'P' then return @__localStartDay + ' ' + @__localStartTime
        when 'F' then return 'Final'
        when 'FO' then return 'Final OT'
        when 'H' then return 'Halftime'
        when '1', '2', '3', '4' then return 'Q' + @__quarter + ' ' + @__clock
        else return 'OT ' + @__clock

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

      @__updates.url = @get_url() if @__updates.eid or
      @__updates.year or
      @__updates.seasonType or
      @__updates.week or
      @__updates.visitorNickname or
      @__updates.homeNickname

      @__updates.status = @get_status() if @__updates.quarter or
      @__updates.startDay or
      @__updates.startTime or
      @__updates.clock

      for observer in @__observers
        observer.observeChange this, 'propertychanged', @__updates if typeof observer.observeChange is 'function'
      @__updates = {}
      return
