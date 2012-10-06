define ->
  return (obj, mixin) ->
    for name, method of mixin
      obj[name] = method
    return
