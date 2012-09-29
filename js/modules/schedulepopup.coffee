define ['jquery', 'qtip'], ($) ->
  return (schedulelink) ->
    url = window.location.href.match /^([^\?]+\/)/
    return unless url
    url = url[1]

    jLinks = $ schedulelink
    jLinks.each ->
      jLink = $ this
      href = jLink.attr 'href'

      season = href.match /y=([0-9]+)/
      return unless season
      season = season[1]

      week = href.match /w=([0-9]+)/
      return unless week
      week = week[1]

      jLink.removeData 'qtip'

      jLink.qtip
        content:
          text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />'
          ajax:
            url: 'index.php'
            data:
              a: 'schedule'
              y: season
              w: week
              o: 'js'
            type: 'GET'
            once: false
          title:
            text: season + '-' + (season * 1 + 1) + ' week ' + week + ' schedule'
            button: true
        position:
          my: 'center'
          at: 'center'
          target: $ window
          effect: false
        show:
          event: 'click'
          solo: true
          modal: true
        hide: false
        style:
          classes: 'ui-tooltip-tote ui-tooltip-modal ui-tooltip-rounded totePopup'
          def: false

      jLink.click ->
        return false

      return
    return
