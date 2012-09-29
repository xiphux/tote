define ['jquery', 'qtip'], ($) ->
  return (feedlink) ->
    $(feedlink).each ->
      jLink = $ this

      poolid = jLink.attr('href').match /p=([^&]+)/
      return unless poolid
      poolid = unescape poolid[1]
      
      content = '<div>Action feed format: '
      content += '<a href="index.php?a=atom&p=' + poolid + '">Atom</a>'
      content += ' | '
      content += '<a href="index.php?a=rss&p=' + poolid + '">RSS</a>'
      content += '</div>'

      jLink.qtip
        content:
          text: content
        show:
          event: 'click'
        hide:
          fixed: true
          delay: 150
        style:
          classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
          def: false
        position:
          viewport: $ window
          effect: false

      jLink.click ->
        return false
      return
    return

