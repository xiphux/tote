define ->
  rv = -1
  if navigator.appName == 'Microsoft Internet Explorer'
    ua = navigator.userAgent;
    re = new RegExp "MSIE ([0-9]{1,}[\.0-9]{0,})"
    if re.exec(ua) != null
      rv = parseFloat RegExp.$1
  return rv
