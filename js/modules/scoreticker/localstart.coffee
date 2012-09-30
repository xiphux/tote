define ['module'], (module) ->
  
  dayToNum = (day) ->
    day = day.toUpperCase()
    switch day
      when 'SUN' then 0
      when 'MON' then 1
      when 'TUE' then 2
      when 'WED' then 3
      when 'THU' then 4
      when 'FRI' then 5
      when 'SAT' then 6
      else -1

  numToDay = (num) ->
    switch num
      when 0 then 'Sun'
      when 1 then 'Mon'
      when 2 then 'Tue'
      when 3 then 'Wed'
      when 4 then 'Thu'
      when 5 then 'Fri'
      when 6 then 'Sat'
      else ''

  return (day, time) ->
    offset = module.config().timezoneoffset
    return day: day, time: time if not offset

    now = new Date()

    dayInt = dayToNum(day)
    return day: day, time: time if dayInt < 0

    now.setTime now.getTime()+86400000 while now.getDay() isnt dayInt

    timepieces = time.split ':'
    hours = parseInt timepieces[0]
    mins = parseInt timepieces[1]
    hours += 12 if hours < 11
    now.setHours hours, mins

    now.setTime now.getTime()+(offset*1000)

    shiftedDay = numToDay now.getDay()
    shiftedHours = now.getHours()
    shiftedHours -= 12 if shiftedHours > 12
    shiftedMin = now.getMinutes()
    shiftedMin = '0' + shiftedMin if shiftedMin < 10
    shiftedTime = shiftedHours + ':' + shiftedMin

    return day: shiftedDay, time: shiftedTime
