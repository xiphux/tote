define ->
  return (messageContainer) ->

    require ['jquery'], ($) ->

      container = $ messageContainer
      
      line1 = $ document.createElement('div')
      line1.text 'Analytics requires features that your browser does not support.'
      container.append line1

      line2 = $ document.createElement('div')
      line2.css 'padding-bottom', '10px'
      line2.text 'Please upgrade to one of the following browsers:'
      container.append line2

      chrome = $ document.createElement('div')
      chromelink = $ document.createElement('a')
      chromelink.attr 'href', 'http://www.google.com/chrome'
      chromelink.attr 'target', '_blank'
      chromelink.text 'Google Chrome'
      chrome.append chromelink
      container.append chrome

      firefox = $ document.createElement('div')
      firefoxlink = $ document.createElement('a')
      firefoxlink.attr 'href', 'http://www.getfirefox.com'
      firefoxlink.attr 'target', '_blank'
      firefoxlink.text 'Mozilla Firefox'
      firefox.append firefoxlink
      container.append firefox

      ie9 = $ document.createElement('div')
      ie9link = $ document.createElement('a')
      ie9link.attr 'href', 'http://windows.microsoft.com/en-us/internet-explorer/products/ie/home'
      ie9link.attr 'target', '_blank'
      ie9link.text 'Internet Explorer 9'
      ie9.append ie9link
      container.append ie9

      return
    return
