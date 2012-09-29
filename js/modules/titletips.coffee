define ['jquery', 'qtip'], ($) ->
  titles = $ '*[title]'
  titles.qtip
    style:
      classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
      def: false
    position:
      viewport: $ window
      my: 'bottom left'
      at: 'top center'
      effect: false
  return
