define ['jquery'], ($) ->
  return (select, submit) ->
    if select
      $(select).change ->
        $(this).closest('form').submit()
        return
    if submit
      $(submit).remove()
    return
