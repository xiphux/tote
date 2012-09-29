define ->
  return (messageContainer) ->

    require ['jquery'], ($) ->

      container = $ messageContainer
      
      line1 = $(document.createElement('div'))
        .text('Analytics requires features that your browser does not support.')
      container.append line1

      line2 = $(document.createElement('div'))
        .css('padding-bottom', '10px')
        .text('Please upgrade to one of the following browsers:')
      container.append line2

      chrome = $ document.createElement('div')
      chromelink = $(document.createElement('a'))
        .attr('href', 'http://www.google.com/chrome')
        .attr('target', '_blank')
        .text('Google Chrome')
      chrome.append chromelink
      container.append chrome

      firefox = $ document.createElement('div')
      firefoxlink = $(document.createElement('a'))
        .attr('href', 'http://www.getfirefox.com')
        .attr('target', '_blank')
        .text('Mozilla Firefox')
      firefox.append firefoxlink
      container.append firefox

      ie9 = $ document.createElement('div')
      ie9link = $(document.createElement('a'))
        .attr('href', 'http://windows.microsoft.com/en-us/internet-explorer/products/ie/home')
        .attr('target', '_blank')
        .text('Internet Explorer 9')
      ie9.append ie9link
      container.append ie9

      return
    return
