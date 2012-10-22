define ['jquery', 'cookies'], ($) ->
  return (section, cookie) ->

    $section = $ section
    $header = $section.find '.sectionHeader'
    $content = $section.find '.sectionContent'

    openText = $header.text()
    closedText = $header.text() + '...'

    visible = false
    if $.cookies.test()
      ck = $.cookies.get cookie
      visible = ck if ck isnt null

    link = $ document.createElement 'a'
    link.attr 'href', '#'

    if visible
      link.text openText
      link.addClass 'sectionOpen'
      $content.show()
    else
      link.text closedText
      link.addClass 'sectionClosed'
      $content.hide()

    link.click ->
      $this = $ @
      newcookievalue = null
      if $content.is ':visible'
        $content.hide 'fast'
        $this.text closedText
        $this.removeClass 'sectionOpen'
        $this.addClass 'sectionClosed'
        newcookievalue = false
      else
        $content.show 'fast', ->
          $('html,body').animate
            scrollTop: $('body').attr 'scrollHeight',
            500
          return
        $this.text openText
        $this.removeClass 'sectionClosed'
        $this.addClass 'sectionOpen'
        newcookievalue = true
      if $.cookies.test()
        exp = new Date()
        exp.setDate exp.getDate()+365
        $.cookies.set cookie, newcookievalue, expiresAt: exp
      return false

    $header.text ''
    $header.append link
    return
