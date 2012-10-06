define ['cs!./localstart', 'cs!modules/utils/mixin', 'cs!modules/utils/observable'], (localStart, mixin, observable) ->
  
  class Game
    constructor: ->
      mixin this, observable
      @__updates = {}

    year: (year) ->
      return @__year if typeof year is 'undefined'

      return @ if year is @__year
      @__year = year
      @__queueUpdates year: year
      return @

    seasonType: (seasonType) ->
      return @__seasonType if typeof seasonType is 'undefined'

      return @ if seasonType is @__seasonType
      @__seasonType = seasonType
      @__queueUpdates seasonType: seasonType
      return @

    week: (week) ->
      return @__week if typeof week is 'undefined'

      return @ if week is @__week
      @__week = week
      @__queueUpdates week: week
      return @

    gsis: (gsis) ->
      return @__gsis if typeof gsis is 'undefined'

      return @ if gsis is @__gsis
      @__gsis = gsis
      @__queueUpdates gsis: gsis
      return @

    eid: (eid) ->
      return @__eid if typeof eid is 'undefined'

      return @ if eid is @__eid
      @__eid = eid
      @__queueUpdates eid: eid
      return @

    start: (day, time) ->
      return day: @__startDay, time: @__startTime if (typeof day is 'undefined') and (typeof time is 'undefined')

      return @ if day is @__startDay and time is @__startTime
      @__startDay = day
      @__startTime = time

      shifted = localStart day, time
      @__localStartDay = shifted.day
      @__localStartTime = shifted.time

      @__queueUpdates
        startDay: day
        startTime: time
      return @

    visitor: (visitor) ->
      return @__visitor if typeof visitor is 'undefined'

      return @ if visitor is @__visitor
      @__visitor = visitor
      @__queueUpdates visitor: visitor
      return @

    visitorNickname: (visitorNickname) ->
      return @__visitorNickname if typeof visitorNickname is 'undefined'

      return @ if visitorNickname is @__visitorNickname
      @__visitorNickname = visitorNickname
      @__queueUpdates visitorNickname: visitorNickname
      return @

    home: (home) ->
      return @__home if typeof home is 'undefined'

      return @ if home is @__home
      @__home = home
      @__queueUpdates home: home
      return @

    homeNickname: (homeNickname) ->
      return @__homeNickname if typeof homeNickname is 'undefined'

      return @ if homeNickname is @__homeNickname
      @__homeNickname = homeNickname
      @__queueUpdates homeNickname: homeNickname
      return @

    homeScore: (homeScore) ->
      return @__homeScore if typeof homeScore is 'undefined'

      return @ if homeScore is @__homeScore
      @__homeScore = homeScore
      @__queueUpdates homeScore: homeScore
      return @

    visitorScore: (visitorScore) ->
      return @__visitorScore if typeof visitorScore is 'undefined'

      return @ if visitorScore is @__visitorScore
      @__visitorScore = visitorScore
      @__queueUpdates visitorScore: visitorScore
      return @

    redZone: (redZone) ->
      return @__redZone if typeof redZone is 'undefined'

      return @ if redZone is @__redZone
      @__redZone = redZone
      @__queueUpdates redZone: redZone
      return @

    quarter: (quarter) ->
      return @__quarter if typeof quarter is 'undefined'

      return @ if quarter is @__quarter
      @__quarter = quarter
      @__queueUpdates quarter: quarter
      return @

    clock: (clock) ->
      return @__clock if typeof clock is 'undefined'

      return @ if clock is @__clock
      @__clock = clock
      @__queueUpdates clock: clock
      return @

    possession: (possession) ->
      return @__possession if typeof possession is 'undefined'

      return @ if possession is @__possession
      @__possession = possession
      @__queueUpdates possession: possession
      return @

    data: (data) ->
      return unless data

      @__delayNotify = true

      for own key, value of data
        switch key
          when 'year' then @year value
          when 'seasonType' then @seasonType value
          when 'week' then @week value
          when 'gsis' then @gsis value
          when 'eid' then @eid value
          when 'start' then @start value.day, value.time if value.day and value.time
          when 'visitor' then @visitor value
          when 'visitorNickname' then @visitorNickname value
          when 'home' then @home value
          when 'homeNickname' then @homeNickname value
          when 'homeScore' then @homeScore value
          when 'visitorScore' then @visitorScore value
          when 'redZone' then @redZone value
          when 'quarter' then @quarter value
          when 'clock' then @clock value
          when 'possession' then @possession value

      @__delayNotify = false
      @__notify()
      return

    playing: ->
      return @__quarter? and
      (@__quarter isnt 'P') and
      (@__quarter isnt 'F') and
      (@__quarter isnt 'FO') and
      (@__quarter isnt 'H')

    active: ->
      return @playing() or @__quarter is 'H'

    url: ->
      typestr = ''
      switch @__seasonType
        when 'P' then typestr = 'PRE'
        when 'R' then typestr = 'REG'
        else return ''
      return 'http://www.nfl.com/gamecenter/' + @__eid + '/' + @__year + '/' + typestr + @__week + '/' + @__visitorNickname + '@' + @__homeNickname

    status: ->
      switch @__quarter
        when 'P' then return @__localStartDay + ' ' + @__localStartTime
        when 'F' then return 'Final'
        when 'FO' then return 'Final OT'
        when 'H' then return 'Halftime'
        when '1', '2', '3', '4' then return 'Q' + @__quarter + ' ' + @__clock
        else return 'OT ' + @__clock

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

      @__updates.url = @url() if @__updates.eid or
      @__updates.year or
      @__updates.seasonType or
      @__updates.week or
      @__updates.visitorNickname or
      @__updates.homeNickname

      @__updates.status = @status() if @__updates.quarter or
      @__updates.startDay or
      @__updates.startTime or
      @__updates.clock

      @__notifyObservers 'propertychanged', @__updates

      @__updates = {}
      return
